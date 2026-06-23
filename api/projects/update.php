<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../helpers/ProjectHelper.php';
require_once __DIR__ . '/../helpers/Notifications.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();
if (!in_array($user['role'], ['admin', 'editor'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Insufficient permissions']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Project ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Check for assignee_id column
    $colStmt = $conn->prepare("SELECT COUNT(*) AS total FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'assignee_id'");
    $colStmt->execute();
    $hasAssignee = ((int)$colStmt->get_result()->fetch_assoc()['total']) > 0;

    // Get current project
    $checkStmt = $conn->prepare("SELECT id, business_unit_id, name, stage, status, owner, owner_id, priority, gate_due, completion_due, progress, current_update, next_steps, budget, actual_cost, currency " . ($hasAssignee ? ", assignee_id" : "") . " FROM projects WHERE id = ?");
    $checkStmt->bind_param("s", $data['id']);
    $checkStmt->execute();
    $currentProject = $checkStmt->get_result()->fetch_assoc();
    if (!$currentProject) {
        http_response_code(404);
        echo json_encode(['error' => 'Project not found']);
        exit;
    }

    $stageChanged = isset($data['stage']) && $data['stage'] !== $currentProject['stage'];
    $conn->begin_transaction();

    $allowedFields = [
        'name' => 's',
        'business_unit_id' => 'i',
        'stage' => 's',
        'status' => 's',
        'owner' => 's',
        'owner_id' => 'i',
        'priority' => 's',
        'gate_due' => 's',
        'completion_due' => 's',
        'progress' => 'i',
        'current_update' => 's',
        'next_steps' => 's',
        'budget' => 'd',
        'actual_cost' => 'd',
        'currency' => 's'
    ];
    if ($hasAssignee) $allowedFields['assignee_id'] = 'i';

    // owner_id is a developer (any role) — keep the legacy `owner` text column in sync
    if (array_key_exists('owner_id', $data) && !empty($data['owner_id'])) {
        $ownerLookup = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
        $ownerLookup->bind_param("i", $data['owner_id']);
        $ownerLookup->execute();
        $ownerRow = $ownerLookup->get_result()->fetch_assoc();
        if (!$ownerRow) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid owner_id']);
            exit;
        }
        $data['owner'] = $ownerRow['full_name'];
    }

    // assignee is the allocator/manager — restricted to editor/admin accounts
    if (array_key_exists('assignee_id', $data) && !empty($data['assignee_id'])) {
        $assigneeRoleStmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $assigneeRoleStmt->bind_param("i", $data['assignee_id']);
        $assigneeRoleStmt->execute();
        $assigneeRoleRow = $assigneeRoleStmt->get_result()->fetch_assoc();
        if (!$assigneeRoleRow || !in_array($assigneeRoleRow['role'], ['admin', 'editor'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Assignee must be an editor or admin']);
            exit;
        }
    }

    $updates = [];
    $params = [];
    $types = '';
    $changedFields = [];

    foreach ($allowedFields as $field => $type) {
        if (array_key_exists($field, $data)) {
            $value = $data[$field];
            if (in_array($field, ['gate_due', 'completion_due'])) $value = !empty($value) ? date('Y-m-d', strtotime($value)) : null;
            if ($field === 'progress') $value = max(0, min(100, (int)$value));
            if (in_array($field, ['budget', 'actual_cost'])) $value = ($value === null || $value === '') ? null : (float)$value;
            $currentValue = $currentProject[$field] ?? null;
            if ($value != $currentValue) {
                $updates[] = "$field = ?";
                $params[] = $value;
                $types .= $type;
                $changedFields[] = $field;
            }
        }
    }

    // Track the real completion moment (distinct from the planned completion_due) for
    // honest developer-speed analytics. Clearing it again if a project is reopened.
    if (in_array('status', $changedFields, true)) {
        if ($data['status'] === 'complete' && $currentProject['status'] !== 'complete') {
            $updates[] = "completed_at = NOW()";
        } elseif ($data['status'] !== 'complete' && $currentProject['status'] === 'complete') {
            $updates[] = "completed_at = NULL";
        }
    }

    if (empty($updates)) {
        echo json_encode(['status' => 'success', 'message' => 'No changes', 'id' => $data['id']]);
        exit;
    }

    $updates[] = "updated_by = ?";
    $params[] = $user['id'];
    $types .= 'i';
    $updates[] = "updated_at = NOW()";
    $params[] = $data['id'];
    $types .= 's';

    $sql = "UPDATE projects SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) throw new Exception('Failed to update: ' . $stmt->error);

    // Reassigning the owner restarts the accept/reject cycle and notifies the new owner
    if (in_array('owner_id', $changedFields, true) && !empty($data['owner_id'])) {
        $projectName = array_key_exists('name', $data) ? $data['name'] : $currentProject['name'];
        notifyNewOwner($conn, $data['id'], (int)$data['owner_id'], $projectName, $user['id']);
    }

    // Retain a history record whenever the update/next-steps text changes
    if (in_array('current_update', $changedFields, true) || in_array('next_steps', $changedFields, true)) {
        $historyUpdate = array_key_exists('current_update', $data) ? $data['current_update'] : $currentProject['current_update'];
        $historyNext = array_key_exists('next_steps', $data) ? $data['next_steps'] : $currentProject['next_steps'];
        $histStmt = $conn->prepare("INSERT INTO project_updates_history (project_id, update_text, next_steps, updated_by) VALUES (?, ?, ?, ?)");
        $histStmt->bind_param("sssi", $data['id'], $historyUpdate, $historyNext, $user['id']);
        $histStmt->execute();
    }

    // 🔥 AUTO-PROGRESS on stage change
    if ($stageChanged) {
        $newProgress = calculateAutoProgress($data['stage'], $conn, $data['id']);
        $progStmt = $conn->prepare("UPDATE projects SET progress = ? WHERE id = ?");
        $progStmt->bind_param("is", $newProgress, $data['id']);
        $progStmt->execute();
        $changedFields[] = 'progress (auto)';
    }

    // Log activity
    $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent) VALUES (?, 'update', 'project', ?, ?, ?, ?)");
    $details = json_encode(['changed_fields' => $changedFields, 'old' => $currentProject, 'new' => $data]);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $logStmt->bind_param("issss", $user['id'], $data['id'], $details, $ip, $ua);
    $logStmt->execute();

    $conn->commit();

    // Fetch updated project and return
    $resultStmt = $conn->prepare("SELECT p.*, b.name as business_unit_name, b.color as business_unit_color, ou.full_name AS owner_name,
        CASE WHEN p.gate_due < CURDATE() AND p.status != 'complete' THEN 1 ELSE 0 END AS is_gate_overdue,
        CASE WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue' ELSE p.status END AS effective_status
        FROM projects p JOIN business_units b ON p.business_unit_id = b.id LEFT JOIN users ou ON p.owner_id = ou.id WHERE p.id = ?");
    $resultStmt->bind_param("s", $data['id']);
    $resultStmt->execute();
    $updatedProject = $resultStmt->get_result()->fetch_assoc();

    echo json_encode([
        'status' => 'success',
        'message' => 'Updated',
        'id' => $data['id'],
        'changed_fields' => $changedFields,
        'data' => $updatedProject
    ]);

} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}