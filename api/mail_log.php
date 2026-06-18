<?php
require_once __DIR__ . '/bootstrap.php';
use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

try {
    $user = Auth::requireAuth();
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $sql = "
        SELECT e.id, e.email_type, e.status, e.message, e.sent_at,
               p.name AS project_name,
               u.full_name AS assignee_name
        FROM email_logs e
        LEFT JOIN projects p ON e.project_id = p.id
        LEFT JOIN users u ON e.assignee_id = u.id
        ORDER BY e.sent_at DESC
        LIMIT 100
    ";
    $result = $conn->query($sql);
    $logs = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'logs' => $logs]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}