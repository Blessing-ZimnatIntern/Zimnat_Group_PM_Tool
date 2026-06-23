<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../helpers/Notifications.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();

// Only editors and admins can create projects
if (!in_array($user['role'], ['admin', 'editor'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Insufficient permissions']);
    exit;
}

// Get input data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($data['name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Project name is required']);
    exit;
}

if (empty($data['business_unit_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Business unit is required']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Validate business unit exists
    $buStmt = $conn->prepare("SELECT id FROM business_units WHERE id = ?");
    $buStmt->bind_param("i", $data['business_unit_id']);
    $buStmt->execute();
    $buResult = $buStmt->get_result();

    if ($buResult->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid business unit']);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    // Generate unique project ID
    $projectId = 'proj_' . bin2hex(random_bytes(16));

    // Set default values
    $stage = $data['stage'] ?? 'initiation';
    $status = $data['status'] ?? 'ontrack';
    $ownerId = !empty($data['owner_id']) ? (int)$data['owner_id'] : null;
    $owner = $data['owner'] ?? '';
    if ($ownerId) {
        $ownerStmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
        $ownerStmt->bind_param("i", $ownerId);
        $ownerStmt->execute();
        $ownerRow = $ownerStmt->get_result()->fetch_assoc();
        if (!$ownerRow) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid owner_id']);
            exit;
        }
        $owner = $ownerRow['full_name'];
    }
    $assigneeId = !empty($data['assignee_id']) ? (int)$data['assignee_id'] : null;
    if ($assigneeId) {
        $assigneeRoleStmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $assigneeRoleStmt->bind_param("i", $assigneeId);
        $assigneeRoleStmt->execute();
        $assigneeRoleRow = $assigneeRoleStmt->get_result()->fetch_assoc();
        if (!$assigneeRoleRow || !in_array($assigneeRoleRow['role'], ['admin', 'editor'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Assignee must be an editor or admin']);
            exit;
        }
    }
    $priority = $data['priority'] ?? 'normal';
    $gateDue = !empty($data['gate_due']) ? date('Y-m-d', strtotime($data['gate_due'])) : null;
    $completionDue = !empty($data['completion_due']) ? date('Y-m-d', strtotime($data['completion_due'])) : null;
    $progress = isset($data['progress']) ? max(0, min(100, (int)$data['progress'])) : 0;
    $currentUpdate = $data['current_update'] ?? '';
    $nextSteps = $data['next_steps'] ?? '';
    $budget = (isset($data['budget']) && $data['budget'] !== '') ? (float)$data['budget'] : null;
    $actualCost = (isset($data['actual_cost']) && $data['actual_cost'] !== '') ? (float)$data['actual_cost'] : null;
    $currency = $data['currency'] ?? 'USD';

    // Check if assignee_id column exists
    $colStmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'projects'
          AND COLUMN_NAME = 'assignee_id'
    ");
    $colStmt->execute();
    $hasAssignee = ((int)$colStmt->get_result()->fetch_assoc()['total']) > 0;

    // Build insert statement dynamically. Each entry is [column, value, bind type]
    // so the field list, placeholders, and bind-type string can never drift out of sync.
    $columns = [
        ['id', $projectId, 's'],
        ['business_unit_id', $data['business_unit_id'], 'i'],
        ['name', $data['name'], 's'],
        ['stage', $stage, 's'],
        ['status', $status, 's'],
        ['owner', $owner, 's'],
        ['owner_id', $ownerId, 'i'],
        ['priority', $priority, 's'],
        ['gate_due', $gateDue, 's'],
        ['completion_due', $completionDue, 's'],
        ['progress', $progress, 'i'],
        ['current_update', $currentUpdate, 's'],
        ['next_steps', $nextSteps, 's'],
        ['budget', $budget, 'd'],
        ['actual_cost', $actualCost, 'd'],
        ['currency', $currency, 's'],
        ['created_by', $user['id'], 'i'],
    ];

    if ($hasAssignee) {
        $columns[] = ['assignee_id', $assigneeId, 'i'];
    }

    $fields = array_merge(array_column($columns, 0), ['created_at', 'updated_at']);
    $placeholders = array_merge(array_fill(0, count($columns), '?'), ['NOW()', 'NOW()']);
    $bindTypes = implode('', array_column($columns, 2));
    $bindValues = array_column($columns, 1);

    $sql = "INSERT INTO projects (" . implode(', ', $fields) . ") 
            VALUES (" . implode(', ', $placeholders) . ")";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($bindTypes, ...$bindValues);

    if (!$stmt->execute()) {
        throw new Exception('Failed to create project: ' . $stmt->error);
    }

    if ($ownerId) {
        notifyNewOwner($conn, $projectId, $ownerId, $data['name'], $user['id']);
    }

    // Initialize governance items for the new project
    $govStmt = $conn->prepare("
        INSERT INTO governance_items (project_id, item_key, item_label, stage_gate, status)
        SELECT ?, item_key, item_label, stage_gate, 'pending'
        FROM governance_items 
        WHERE project_id IS NULL
        GROUP BY item_key
    ");

    // If there are no default governance items, insert them
    $checkDefault = $conn->query("SELECT COUNT(*) FROM governance_items WHERE project_id IS NULL");
    if ($checkDefault->fetch_row()[0] == 0) {
        $defaultItems = [
            'boscard' => ['BOSCARD', 'initiation'],
            'brd' => ['Business Requirements Document (BRD)', 'planning'],
            'plan' => ['Project Plan / Gantt Chart', 'planning'],
            'qa_report' => ['Quality Assurance Testing Report', 'qa'],
            'uat_signoff' => ['UAT Sign-offs', 'uat'],
            'golive_cr' => ['Go-live Change Request Sign-off', 'closure'],
            'closure_report' => ['Closure Report Sign-off', 'closure']
        ];
        foreach ($defaultItems as $key => $item) {
            $insertGov = $conn->prepare("
                INSERT INTO governance_items (item_key, item_label, stage_gate) 
                VALUES (?, ?, ?)
            ");
            $insertGov->bind_param("sss", $key, $item[0], $item[1]);
            $insertGov->execute();
        }
        // Now insert for the new project
        $govStmt = $conn->prepare("
            INSERT INTO governance_items (project_id, item_key, item_label, stage_gate, status)
            SELECT ?, item_key, item_label, stage_gate, 'pending'
            FROM governance_items 
            WHERE project_id IS NULL
        ");
    }

    $govStmt->bind_param("s", $projectId);
    $govStmt->execute();

    // Log activity
    $logStmt = $conn->prepare("
        INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
        VALUES (?, 'create', 'project', ?, ?, ?, ?)
    ");
    $details = json_encode($data);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $logStmt->bind_param("issss", $user['id'], $projectId, $details, $ip, $userAgent);
    $logStmt->execute();

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Project created successfully',
        'id' => $projectId,
        'data' => [
            'id' => $projectId,
            'name' => $data['name'],
            'business_unit_id' => $data['business_unit_id'],
            'stage' => $stage,
            'status' => $status,
            'owner' => $owner,
            'owner_id' => $ownerId,
            'assignee_id' => $assigneeId,
            'priority' => $priority,
            'gate_due' => $gateDue,
            'completion_due' => $completionDue,
            'progress' => $progress
        ]
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage(),
        'trace' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true' ? $e->getTraceAsString() : null
    ]);
}