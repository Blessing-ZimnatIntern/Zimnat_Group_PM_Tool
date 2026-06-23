<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireRole('editor');
$data = json_decode(file_get_contents('php://input'), true) ?: [];

$projectId = $data['project_id'] ?? '';
$name = trim($data['name'] ?? '');
$roleTitle = trim($data['role_title'] ?? '');
$notes = trim($data['notes'] ?? '');

if (!$projectId || !$name) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'project_id and name are required']);
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

    $stmt = $conn->prepare("INSERT INTO stakeholders (project_id, name, role_title, notes, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $projectId, $name, $roleTitle, $notes, $user['id']);
    $stmt->execute();
    $id = $stmt->insert_id;

    $logStmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent) VALUES (?, 'stakeholder_add', 'project', ?, ?, ?, ?)");
    $details = json_encode(['name' => $name]);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $logStmt->bind_param("issss", $user['id'], $projectId, $details, $ip, $ua);
    $logStmt->execute();

    echo json_encode(['status' => 'success', 'data' => [
        'id' => $id, 'project_id' => $projectId, 'name' => $name, 'role_title' => $roleTitle, 'notes' => $notes, 'documents' => []
    ]]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
