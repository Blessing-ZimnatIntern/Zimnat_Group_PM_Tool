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
        SELECT t.id, t.project_id, t.date, t.hours, t.description, t.created_at, u.full_name AS logged_by
        FROM time_entries t
        JOIN users u ON t.user_id = u.id
        WHERE t.project_id = ?
        ORDER BY t.date DESC, t.created_at DESC
    ");
    $stmt->bind_param("s", $projectId);
    $stmt->execute();
    $entries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $totalStmt = $conn->prepare("SELECT COALESCE(SUM(hours), 0) AS total_hours FROM time_entries WHERE project_id = ?");
    $totalStmt->bind_param("s", $projectId);
    $totalStmt->execute();
    $totalHours = (float)$totalStmt->get_result()->fetch_assoc()['total_hours'];

    echo json_encode(['status' => 'success', 'data' => $entries, 'total_hours' => $totalHours]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
