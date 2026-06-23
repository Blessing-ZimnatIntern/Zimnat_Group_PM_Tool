<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireRole('editor');

$projectId = $_POST['project_id'] ?? '';
$itemKey = $_POST['item_key'] ?? '';

if (!$projectId || !$itemKey) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'project_id and item_key are required']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'No file uploaded or upload error']);
    exit;
}

$maxSize = (int)($_ENV['GOVERNANCE_MAX_UPLOAD_SIZE'] ?? 10485760);
$allowedExt = array_map('trim', explode(',', $_ENV['GOVERNANCE_ALLOWED_FILE_TYPES'] ?? 'pdf,doc,docx,xls,xlsx,png,jpg,jpeg'));
$uploadPath = rtrim($_ENV['GOVERNANCE_UPLOAD_PATH'] ?? 'uploads/governance', '/');
$uploadDir = BASE_PATH . '/' . $uploadPath;

$file = $_FILES['file'];

if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'File exceeds the ' . round($maxSize / 1048576, 1) . 'MB limit']);
    exit;
}

$originalName = $file['name'];
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'File type not allowed. Allowed: ' . implode(', ', $allowedExt)]);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();

    $projectStmt = $conn->prepare("SELECT id FROM projects WHERE id = ?");
    $projectStmt->bind_param("s", $projectId);
    $projectStmt->execute();
    if ($projectStmt->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'error' => 'Project not found']);
        exit;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0750, true);
    }
    if (!file_exists(BASE_PATH . '/uploads/.htaccess')) {
        @file_put_contents(BASE_PATH . '/uploads/.htaccess', "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n");
    }

    // Randomized stored filename — never trust the uploaded name for the filesystem path
    $storedName = bin2hex(random_bytes(20)) . '.' . $ext;
    $destination = $uploadDir . '/' . $storedName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to save the uploaded file');
    }

    $mimeType = mime_content_type($destination) ?: $file['type'];

    $stmt = $conn->prepare("
        INSERT INTO governance_attachments (project_id, item_key, original_name, stored_name, mime_type, size_bytes, uploaded_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssii", $projectId, $itemKey, $originalName, $storedName, $mimeType, $file['size'], $user['id']);
    $stmt->execute();
    $attachmentId = $stmt->insert_id;

    $logStmt = $conn->prepare("
        INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
        VALUES (?, 'attachment_upload', 'project', ?, ?, ?, ?)
    ");
    $details = json_encode(['item_key' => $itemKey, 'file_name' => $originalName]);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $logStmt->bind_param("issss", $user['id'], $projectId, $details, $ip, $ua);
    $logStmt->execute();

    echo json_encode([
        'status' => 'success',
        'data' => [
            'id' => $attachmentId,
            'project_id' => $projectId,
            'item_key' => $itemKey,
            'original_name' => $originalName,
            'size_bytes' => (int)$file['size'],
            'uploaded_by' => $user['full_name'] ?? $user['username'],
            'uploaded_at' => date('Y-m-d H:i:s'),
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
