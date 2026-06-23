<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();
$projectId = $_GET['project_id'] ?? '';

if (!$projectId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'project_id is required']);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();

    $stmt = $conn->prepare("
        SELECT a.id, a.project_id, a.item_key, a.original_name, a.size_bytes, a.uploaded_at, u.full_name AS uploaded_by
        FROM governance_attachments a
        LEFT JOIN users u ON a.uploaded_by = u.id
        WHERE a.project_id = ?
        ORDER BY a.uploaded_at DESC
    ");
    $stmt->bind_param("s", $projectId);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
