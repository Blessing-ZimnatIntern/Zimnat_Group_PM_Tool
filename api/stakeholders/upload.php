<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireRole('editor');

$stakeholderId = (int)($_POST['stakeholder_id'] ?? 0);

if (!$stakeholderId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'stakeholder_id is required']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'No file uploaded or upload error']);
    exit;
}

$maxSize = (int)($_ENV['STAKEHOLDER_MAX_UPLOAD_SIZE'] ?? 10485760);
$allowedExt = array_map('trim', explode(',', $_ENV['STAKEHOLDER_ALLOWED_FILE_TYPES'] ?? 'pdf,doc,docx,txt'));
$uploadPath = rtrim($_ENV['STAKEHOLDER_UPLOAD_PATH'] ?? 'uploads/stakeholders', '/');
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

    $stakeholderStmt = $conn->prepare("SELECT id, project_id FROM stakeholders WHERE id = ?");
    $stakeholderStmt->bind_param("i", $stakeholderId);
    $stakeholderStmt->execute();
    $stakeholder = $stakeholderStmt->get_result()->fetch_assoc();
    if (!$stakeholder) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'error' => 'Stakeholder not found']);
        exit;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0750, true);
    }
    if (!file_exists(BASE_PATH . '/uploads/.htaccess')) {
        @file_put_contents(BASE_PATH . '/uploads/.htaccess', "<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Deny from all\n</IfModule>\n");
    }

    $storedName = bin2hex(random_bytes(20)) . '.' . $ext;
    $destination = $uploadDir . '/' . $storedName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Failed to save the uploaded file');
    }

    $mimeType = mime_content_type($destination) ?: $file['type'];

    $stmt = $conn->prepare("
        INSERT INTO stakeholder_documents (stakeholder_id, original_name, stored_name, mime_type, size_bytes, uploaded_by)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issssi", $stakeholderId, $originalName, $storedName, $mimeType, $file['size'], $user['id']);
    $stmt->execute();
    $docId = $stmt->insert_id;

    $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent) VALUES (?, 'stakeholder_document_upload', 'project', ?, ?, ?, ?)");
    $details = json_encode(['stakeholder_id' => $stakeholderId, 'file_name' => $originalName]);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $logStmt->bind_param("issss", $user['id'], $stakeholder['project_id'], $details, $ip, $ua);
    $logStmt->execute();

    echo json_encode(['status' => 'success', 'data' => [
        'id' => $docId,
        'stakeholder_id' => $stakeholderId,
        'original_name' => $originalName,
        'size_bytes' => (int)$file['size'],
        'uploaded_at' => date('Y-m-d H:i:s'),
    ]]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
