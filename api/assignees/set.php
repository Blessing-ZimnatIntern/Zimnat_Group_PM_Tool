<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireRole('editor');
$data = json_decode(file_get_contents('php://input'), true) ?: [];

$projectId = $data['project_id'] ?? '';
$assignees = $data['assignees'] ?? [];

if (!$projectId || !is_array($assignees)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'project_id and an assignees array are required']);
    exit;
}

// Normalize + validate rows
$rows = [];
foreach ($assignees as $a) {
    $userId = (int)($a['user_id'] ?? 0);
    $pct = (int)($a['allocation_pct'] ?? 100);
    if ($userId <= 0) continue;
    $pct = max(1, min(100, $pct));
    $rows[$userId] = $pct; // de-dupe by user_id, last value wins
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

    // Assignee = the allocator/manager who assigned this project out — restricted to editor/admin
    if (!empty($rows)) {
        $userIds = array_keys($rows);
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $types = str_repeat('i', count($userIds));
        $roleStmt = $conn->prepare("SELECT id, full_name, role FROM users WHERE id IN ($placeholders)");
        $roleStmt->bind_param($types, ...$userIds);
        $roleStmt->execute();
        $roleRows = $roleStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $foundIds = array_column($roleRows, 'id');
        foreach ($userIds as $uid) {
            if (!in_array($uid, $foundIds)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'error' => "User $uid not found"]);
                exit;
            }
        }
        foreach ($roleRows as $r) {
            if (!in_array($r['role'], ['admin', 'editor'])) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'error' => "{$r['full_name']} is a viewer — assignees must be an editor or admin"]);
                exit;
            }
        }
    }

    $conn->begin_transaction();

    $delStmt = $conn->prepare("DELETE FROM project_assignees WHERE project_id = ?");
    $delStmt->bind_param("s", $projectId);
    $delStmt->execute();

    $insStmt = $conn->prepare("INSERT INTO project_assignees (project_id, user_id, allocation_pct) VALUES (?, ?, ?)");
    foreach ($rows as $userId => $pct) {
        $insStmt->bind_param("sii", $projectId, $userId, $pct);
        $insStmt->execute();
    }

    $conn->commit();

    // Warn (non-blocking) if any assigned user is now over 100% allocated across all their projects
    $warnings = [];
    if (!empty($rows)) {
        $userIds = array_keys($rows);
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $types = str_repeat('i', count($userIds));
        $totalsStmt = $conn->prepare("
            SELECT pa.user_id, u.full_name, SUM(pa.allocation_pct) AS total_pct
            FROM project_assignees pa
            JOIN users u ON pa.user_id = u.id
            WHERE pa.user_id IN ($placeholders)
            GROUP BY pa.user_id, u.full_name
            HAVING total_pct > 100
        ");
        $totalsStmt->bind_param($types, ...$userIds);
        $totalsStmt->execute();
        $warnings = $totalsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    echo json_encode(['status' => 'success', 'warnings' => $warnings]);
} catch (Throwable $e) {
    if (isset($conn)) $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
