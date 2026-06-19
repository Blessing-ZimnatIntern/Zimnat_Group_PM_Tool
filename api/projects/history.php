<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();
$projectId = $_GET['id'] ?? null;

if (!$projectId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'Project ID required']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $sql = "SELECT h.update_text, h.next_steps, h.created_at, u.full_name 
            FROM project_updates_history h
            JOIN users u ON h.updated_by = u.id
            WHERE h.project_id = ? 
            ORDER BY h.created_at DESC LIMIT 50";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $history]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}