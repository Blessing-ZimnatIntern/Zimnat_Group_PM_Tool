<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireRole('editor');
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'Missing attachment id']);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();

    $stmt = $conn->prepare("SELECT project_id, item_key, original_name, stored_name FROM governance_attachments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $attachment = $stmt->get_result()->fetch_assoc();

    if (!$attachment) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'error' => 'Attachment not found']);
        exit;
    }

    $uploadPath = rtrim($_ENV['GOVERNANCE_UPLOAD_PATH'] ?? 'uploads/governance', '/');
    $path = BASE_PATH . '/' . $uploadPath . '/' . $attachment['stored_name'];

    $delStmt = $conn->prepare("DELETE FROM governance_attachments WHERE id = ?");
    $delStmt->bind_param("i", $id);
    $delStmt->execute();

    if (is_file($path)) {
        @unlink($path);
    }

    $logStmt = $conn->prepare("
        INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
        VALUES (?, 'attachment_delete', 'project', ?, ?, ?, ?)
    ");
    $details = json_encode(['item_key' => $attachment['item_key'], 'file_name' => $attachment['original_name']]);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $logStmt->bind_param("issss", $user['id'], $attachment['project_id'], $details, $ip, $ua);
    $logStmt->execute();

    echo json_encode(['status' => 'success']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
