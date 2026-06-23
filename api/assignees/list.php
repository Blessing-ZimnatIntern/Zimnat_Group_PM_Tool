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
        SELECT pa.user_id, pa.allocation_pct, u.full_name
        FROM project_assignees pa
        JOIN users u ON pa.user_id = u.id
        WHERE pa.project_id = ?
        ORDER BY u.full_name
    ");
    $stmt->bind_param("s", $projectId);
    $stmt->execute();
    $assignees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $assignees]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
