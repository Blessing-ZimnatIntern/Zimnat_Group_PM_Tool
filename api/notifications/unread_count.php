<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();

try {
    $conn = Database::getInstance()->getConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $count = (int)$stmt->get_result()->fetch_assoc()['total'];

    echo json_encode(['status' => 'success', 'data' => ['count' => $count]]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
