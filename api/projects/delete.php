<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireRole('editor');

$id = $_GET['id'] ?? null;
if (!$id) {
    $data = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = $data['id'] ?? null;
}

if (!$id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'Project ID is required']);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();
    $conn->begin_transaction();

    $projectStmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
    $projectStmt->bind_param("s", $id);
    $projectStmt->execute();
    $project = $projectStmt->get_result()->fetch_assoc();
    if (!$project) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'error' => 'Project not found']);
        exit;
    }

    $colStmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'projects'
          AND COLUMN_NAME = 'deleted_at'
    ");
    $colStmt->execute();
    $hasDeletedAt = ((int)$colStmt->get_result()->fetch_assoc()['total']) > 0;

    if ($hasDeletedAt) {
        $deleteStmt = $conn->prepare("UPDATE projects SET deleted_at = NOW(), updated_by = ?, updated_at = NOW() WHERE id = ?");
        $deleteStmt->bind_param("is", $user['id'], $id);
        $deleteStmt->execute();
        $mode = 'soft';
    } else {
        $childStmt = $conn->prepare("DELETE FROM governance_items WHERE project_id = ?");
        $childStmt->bind_param("s", $id);
        $childStmt->execute();

        $historyStmt = $conn->prepare("DELETE FROM project_updates_history WHERE project_id = ?");
        $historyStmt->bind_param("s", $id);
        $historyStmt->execute();

        $deleteStmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
        $deleteStmt->bind_param("s", $id);
        $deleteStmt->execute();
        $mode = 'hard';
    }

    $logStmt = $conn->prepare("
        INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
        VALUES (?, 'delete', 'project', ?, ?, ?, ?)
    ");
    $details = json_encode(['mode' => $mode, 'project' => $project]);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $logStmt->bind_param("issss", $user['id'], $id, $details, $ip, $userAgent);
    $logStmt->execute();

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Project deleted', 'mode' => $mode, 'id' => $id]);
} catch (Throwable $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
