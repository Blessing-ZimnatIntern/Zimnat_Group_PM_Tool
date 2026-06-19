<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../helpers/ProjectHelper.php';

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
    $checkStmt = $conn->prepare("SELECT id, business_unit_id, name, stage, status, owner, priority, gate_due, completion_due, progress, current_update, next_steps " . ($hasAssignee ? ", assignee_id" : "") . " FROM projects WHERE id = ?");
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
        'priority' => 's',
        'gate_due' => 's',
        'completion_due' => 's',
        'progress' => 'i',
        'current_update' => 's',
        'next_steps' => 's'
    ];
    if ($hasAssignee) $allowedFields['assignee_id'] = 'i';

    $updates = [];
    $params = [];
    $types = '';
    $changedFields = [];

    foreach ($allowedFields as $field => $type) {
        if (array_key_exists($field, $data)) {
            $value = $data[$field];
            if (in_array($field, ['gate_due', 'completion_due'])) $value = !empty($value) ? date('Y-m-d', strtotime($value)) : null;
            if ($field === 'progress') $value = max(0, min(100, (int)$value));
            $currentValue = $currentProject[$field] ?? null;
            if ($value != $currentValue) {
                $updates[] = "$field = ?";
                $params[] = $value;
                $types .= $type;
                $changedFields[] = $field;
            }
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
    $resultStmt = $conn->prepare("SELECT p.*, b.name as business_unit_name, b.color as business_unit_color, 
        CASE WHEN p.gate_due < CURDATE() AND p.status != 'complete' THEN 1 ELSE 0 END AS is_gate_overdue,
        CASE WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue' ELSE p.status END AS effective_status
        FROM projects p JOIN business_units b ON p.business_unit_id = b.id WHERE p.id = ?");
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