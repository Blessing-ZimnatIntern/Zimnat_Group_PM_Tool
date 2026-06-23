<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireRole('editor');
$data = json_decode(file_get_contents('php://input'), true) ?: [];
$id = (int)($data['id'] ?? 0);

if (!$id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'id is required']);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();

    $stmt = $conn->prepare("SELECT stored_name FROM stakeholder_documents WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $doc = $stmt->get_result()->fetch_assoc();
    if (!$doc) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'error' => 'Document not found']);
        exit;
    }

    $uploadPath = rtrim($_ENV['STAKEHOLDER_UPLOAD_PATH'] ?? 'uploads/stakeholders', '/');
    $path = BASE_PATH . '/' . $uploadPath . '/' . $doc['stored_name'];
    if (is_file($path)) @unlink($path);

    $delStmt = $conn->prepare("DELETE FROM stakeholder_documents WHERE id = ?");
    $delStmt->bind_param("i", $id);
    $delStmt->execute();

    echo json_encode(['status' => 'success']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
