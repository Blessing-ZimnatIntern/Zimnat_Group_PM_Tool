<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();

try {
    $conn = Database::getInstance()->getConnection();
    $result = $conn->query("
        SELECT DISTINCT s.name
        FROM stakeholders s
        JOIN projects p ON s.project_id = p.id
        WHERE p.deleted_at IS NULL
        ORDER BY s.name
    ");
    $names = array_column($result->fetch_all(MYSQLI_ASSOC), 'name');
    echo json_encode(['status' => 'success', 'data' => $names]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
