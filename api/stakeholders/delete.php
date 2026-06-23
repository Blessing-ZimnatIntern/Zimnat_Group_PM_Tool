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

    $uploadPath = rtrim($_ENV['STAKEHOLDER_UPLOAD_PATH'] ?? 'uploads/stakeholders', '/');
    $docStmt = $conn->prepare("SELECT stored_name FROM stakeholder_documents WHERE stakeholder_id = ?");
    $docStmt->bind_param("i", $id);
    $docStmt->execute();
    foreach ($docStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $doc) {
        $path = BASE_PATH . '/' . $uploadPath . '/' . $doc['stored_name'];
        if (is_file($path)) @unlink($path);
    }

    $stmt = $conn->prepare("DELETE FROM stakeholders WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(['status' => 'success']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
