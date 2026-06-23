<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'id is required']);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();

    $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $comment = $stmt->get_result()->fetch_assoc();
    if (!$comment) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'error' => 'Comment not found']);
        exit;
    }
    if ((int)$comment['user_id'] !== (int)$user['id'] && !in_array($user['role'], ['admin', 'editor'], true)) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'error' => 'Insufficient permissions']);
        exit;
    }

    $delStmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $delStmt->bind_param("i", $id);
    $delStmt->execute();

    echo json_encode(['status' => 'success']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
