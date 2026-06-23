<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

$user = Auth::requireAuth();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    http_response_code(400);
    exit('Missing attachment id');
}

$conn = Database::getInstance()->getConnection();
$stmt = $conn->prepare("SELECT original_name, stored_name, mime_type FROM governance_attachments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$attachment = $stmt->get_result()->fetch_assoc();

if (!$attachment) {
    http_response_code(404);
    exit('Attachment not found');
}

$uploadPath = rtrim($_ENV['GOVERNANCE_UPLOAD_PATH'] ?? 'uploads/governance', '/');
$path = BASE_PATH . '/' . $uploadPath . '/' . $attachment['stored_name'];

if (!is_file($path)) {
    http_response_code(404);
    exit('File missing on disk');
}

header('Content-Type: ' . ($attachment['mime_type'] ?: 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . basename($attachment['original_name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
