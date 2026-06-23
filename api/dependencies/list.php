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

    // Projects this one depends on (blockers), with their current status
    $stmt = $conn->prepare("
        SELECT pd.depends_on_project_id AS project_id, p.name, p.stage,
               CASE WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue' ELSE p.status END AS effective_status
        FROM project_dependencies pd
        JOIN projects p ON p.id = pd.depends_on_project_id
        WHERE pd.project_id = ?
        ORDER BY p.name
    ");
    $stmt->bind_param("s", $projectId);
    $stmt->execute();
    $blockedBy = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Projects that depend on this one
    $stmt2 = $conn->prepare("
        SELECT pd.project_id, p.name, p.stage,
               CASE WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue' ELSE p.status END AS effective_status
        FROM project_dependencies pd
        JOIN projects p ON p.id = pd.project_id
        WHERE pd.depends_on_project_id = ?
        ORDER BY p.name
    ");
    $stmt2->bind_param("s", $projectId);
    $stmt2->execute();
    $blocking = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'blocked_by' => $blockedBy, 'blocking' => $blocking]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
