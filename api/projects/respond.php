<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../helpers/Notifications.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();
$data = json_decode(file_get_contents('php://input'), true) ?: [];

$projectId = $data['project_id'] ?? '';
$action = $data['action'] ?? ''; // 'accept' or 'reject'
$reason = trim($data['reason'] ?? '');

if (!$projectId || !in_array($action, ['accept', 'reject'], true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'project_id and a valid action (accept/reject) are required']);
    exit;
}
if ($action === 'reject' && $reason === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'A reason is required to reject a project allocation']);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();

    $stmt = $conn->prepare("SELECT id, name, owner_id FROM projects WHERE id = ?");
    $stmt->bind_param("s", $projectId);
    $stmt->execute();
    $project = $stmt->get_result()->fetch_assoc();
    if (!$project) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'error' => 'Project not found']);
        exit;
    }
    if ((int)$project['owner_id'] !== (int)$user['id']) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'error' => 'Only the assigned owner can respond to this allocation']);
        exit;
    }

    if ($action === 'accept') {
        $upd = $conn->prepare("UPDATE projects SET owner_acceptance_status = 'accepted', owner_rejection_reason = NULL WHERE id = ?");
        $upd->bind_param("s", $projectId);
        $upd->execute();
        logAllocation($conn, $projectId, $project['owner_id'], 'accepted', null, $user['id']);
    } else {
        $upd = $conn->prepare("UPDATE projects SET owner_acceptance_status = 'rejected', owner_rejection_reason = ? WHERE id = ?");
        $upd->bind_param("ss", $reason, $projectId);
        $upd->execute();
        logAllocation($conn, $projectId, $project['owner_id'], 'rejected', $reason, $user['id']);
        notifyAssignees(
            $conn,
            $projectId,
            'project_rejected',
            "{$user['full_name']} rejected the allocation for \"{$project['name']}\": $reason",
            $user['id']
        );
    }

    echo json_encode(['status' => 'success']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
