<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();

try {
    $conn = Database::getInstance()->getConnection();
    $stmt = $conn->prepare("
        SELECT n.id, n.project_id, p.name AS project_name, n.type, n.message, n.is_read, n.created_at,
               u.full_name AS related_user_name, p.owner_acceptance_status
        FROM notifications n
        JOIN projects p ON n.project_id = p.id
        LEFT JOIN users u ON n.related_user_id = u.id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT 50
    ");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $notifications]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
