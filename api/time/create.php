<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();
$data = json_decode(file_get_contents('php://input'), true) ?: [];

$projectId = $data['project_id'] ?? '';
$date = $data['date'] ?? '';
$hours = isset($data['hours']) ? (float)$data['hours'] : null;
$description = $data['description'] ?? '';

if (!$projectId || !$date || $hours === null || $hours <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'project_id, date and a positive hours value are required']);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();

    $projectStmt = $conn->prepare("SELECT id FROM projects WHERE id = ?");
    $projectStmt->bind_param("s", $projectId);
    $projectStmt->execute();
    if ($projectStmt->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'error' => 'Project not found']);
        exit;
    }

    $dateFormatted = date('Y-m-d', strtotime($date));

    $stmt = $conn->prepare("INSERT INTO time_entries (project_id, user_id, date, hours, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisds", $projectId, $user['id'], $dateFormatted, $hours, $description);
    $stmt->execute();

    $totalStmt = $conn->prepare("SELECT COALESCE(SUM(hours), 0) AS total_hours FROM time_entries WHERE project_id = ?");
    $totalStmt->bind_param("s", $projectId);
    $totalStmt->execute();
    $totalHours = (float)$totalStmt->get_result()->fetch_assoc()['total_hours'];

    echo json_encode(['status' => 'success', 'id' => $stmt->insert_id, 'total_hours' => $totalHours]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
