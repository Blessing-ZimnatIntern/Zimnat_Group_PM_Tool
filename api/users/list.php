<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Fetch active users who are editors/admins (or all, depending on your permission model)
    $sql = "SELECT id, full_name, email FROM users WHERE is_active = 1 ORDER BY full_name";
    $result = $conn->query($sql);
    $users = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $users]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}