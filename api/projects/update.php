<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load bootstrap
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

// Set JSON response header
header('Content-Type: application/json');

// Check authentication
$user = Auth::requireAuth();

// Only editors and admins can update projects
if (!in_array($user['role'], ['admin', 'editor'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Insufficient permissions']);
    exit;
}

// Get input data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Project ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Check if assignee column exists
    $colStmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'projects'
          AND COLUMN_NAME = 'assignee'
    ");
    $colStmt->execute();
    $hasAssignee = ((int)$colStmt->get_result()->fetch_assoc()['total']) > 0;

    // Check if project exists and get current data
    $checkStmt = $conn->prepare("
        SELECT id, business_unit_id, name, stage, status, owner, priority, 
               gate_due, completion_due, progress, current_update, next_steps
               " . ($hasAssignee ? ", assignee" : "") . "
        FROM projects 
        WHERE id = ?
    ");
    $checkStmt->bind_param("s", $data['id']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $currentProject = $checkResult->fetch_assoc();

    if (!$currentProject) {
        http_response_code(404);
        echo json_encode(['error' => 'Project not found']);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    // Build update query dynamically based on provided fields
    $allowedFields = [
        'name' => 's',
        'business_unit_id' => 'i',
        'stage' => 's',
        'status' => 's',
        'owner' => 's',
        'priority' => 's',
        'gate_due' => 's',
        'completion_due' => 's',
        'progress' => 'i',
        'current_update' => 's',
        'next_steps' => 's'
    ];

    if ($hasAssignee) {
        $allowedFields['assignee'] = 's';
    }

    $updates = [];
    $params = [];
    $types = '';
    $changedFields = [];

    foreach ($allowedFields as $field => $type) {
        if (array_key_exists($field, $data)) {
            $value = $data[$field];

            // Special handling for dates
            if (in_array($field, ['gate_due', 'completion_due'])) {
                $value = !empty($value) ? date('Y-m-d', strtotime($value)) : null;
            }

            // Special handling for progress
            if ($field === 'progress') {
                $value = max(0, min(100, (int)$value));
            }

            // Check if value actually changed
            $currentValue = $currentProject[$field] ?? null;
            if ($field === 'business_unit_id') {
                // Validate business unit exists
                if ($value != $currentValue) {
                    $buStmt = $conn->prepare("SELECT id FROM business_units WHERE id = ?");
                    $buStmt->bind_param("i", $value);
                    $buStmt->execute();
                    if ($buStmt->get_result()->num_rows === 0) {
                        throw new Exception('Invalid business unit');
                    }
                }
            }

            if ($value != $currentValue) {
                $updates[] = "$field = ?";
                $params[] = $value;
                $types .= $type;
                $changedFields[] = $field;
            }
        }
    }

    // If no changes, return success early
    if (empty($updates)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'No changes detected',
            'id' => $data['id']
        ]);
        exit;
    }

    // Add updated_by and updated_at
    $updates[] = "updated_by = ?";
    $params[] = $user['id'];
    $types .= 'i';

    $updates[] = "updated_at = NOW()";

    // Add project ID to params
    $params[] = $data['id'];
    $types .= 's';

    // Build and execute update query
    $sql = "UPDATE projects SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        throw new Exception('Failed to update project: ' . $stmt->error);
    }

    // Log the update
    $logStmt = $conn->prepare("
        INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
        VALUES (?, 'update', 'project', ?, ?, ?, ?)
    ");

    $details = json_encode([
        'changed_fields' => $changedFields,
        'old_values' => array_intersect_key($currentProject, array_flip($changedFields)),
        'new_values' => array_intersect_key($data, array_flip($changedFields))
    ]);

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $logStmt->bind_param("issss", $user['id'], $data['id'], $details, $ip, $userAgent);
    $logStmt->execute();

    // Commit transaction
    $conn->commit();

    // Get updated project data
    $resultStmt = $conn->prepare("
        SELECT p.*, b.name as business_unit_name, b.color as business_unit_color
        FROM projects p
        JOIN business_units b ON p.business_unit_id = b.id
        WHERE p.id = ?
    ");
    $resultStmt->bind_param("s", $data['id']);
    $resultStmt->execute();
    $updatedProject = $resultStmt->get_result()->fetch_assoc();

    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Project updated successfully',
        'id' => $data['id'],
        'changed_fields' => $changedFields,
        'data' => $updatedProject
    ]);

} catch (Exception $e) {
    // Rollback on error
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