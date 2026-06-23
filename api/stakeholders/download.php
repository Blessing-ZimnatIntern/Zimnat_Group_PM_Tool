<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

$user = Auth::requireAuth();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    http_response_code(400);
    exit('Missing document id');
}

$conn = Database::getInstance()->getConnection();
$stmt = $conn->prepare("SELECT original_name, stored_name, mime_type FROM stakeholder_documents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$doc = $stmt->get_result()->fetch_assoc();

if (!$doc) {
    http_response_code(404);
    exit('Document not found');
}

$uploadPath = rtrim($_ENV['STAKEHOLDER_UPLOAD_PATH'] ?? 'uploads/stakeholders', '/');
$path = BASE_PATH . '/' . $uploadPath . '/' . $doc['stored_name'];

if (!is_file($path)) {
    http_response_code(404);
    exit('File missing on disk');
}

header('Content-Type: ' . ($doc['mime_type'] ?: 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . basename($doc['original_name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
