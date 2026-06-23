<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireRole('editor');
$data = json_decode(file_get_contents('php://input'), true) ?: [];

$projectId = $data['project_id'] ?? '';
$dependsOn = array_values(array_unique(array_filter($data['depends_on'] ?? [])));

if (!$projectId || !is_array($dependsOn)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'project_id and a depends_on array are required']);
    exit;
}

$dependsOn = array_filter($dependsOn, fn($id) => $id !== $projectId);

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

    // Build the existing dependency graph, excluding this project's own (about-to-be-replaced) edges
    $graph = [];
    $allEdges = $conn->query("SELECT project_id, depends_on_project_id FROM project_dependencies WHERE project_id != '" . $conn->real_escape_string($projectId) . "'");
    while ($row = $allEdges->fetch_assoc()) {
        $graph[$row['project_id']][] = $row['depends_on_project_id'];
    }

    // Reject any new edge that would create a cycle: project_id -> X is a cycle if X can already reach project_id
    $canReach = function ($start, $target, $graph) {
        $visited = [];
        $stack = [$start];
        while ($stack) {
            $node = array_pop($stack);
            if ($node === $target) return true;
            if (isset($visited[$node])) continue;
            $visited[$node] = true;
            foreach ($graph[$node] ?? [] as $next) $stack[] = $next;
        }
        return false;
    };

    $rejected = [];
    $accepted = [];
    foreach ($dependsOn as $depId) {
        if ($canReach($depId, $projectId, $graph)) {
            $rejected[] = $depId;
        } else {
            $accepted[] = $depId;
            $graph[$projectId][] = $depId; // tentatively add so later candidates see it
        }
    }

    $conn->begin_transaction();

    $delStmt = $conn->prepare("DELETE FROM project_dependencies WHERE project_id = ?");
    $delStmt->bind_param("s", $projectId);
    $delStmt->execute();

    if (!empty($accepted)) {
        $insStmt = $conn->prepare("INSERT INTO project_dependencies (project_id, depends_on_project_id) VALUES (?, ?)");
        foreach ($accepted as $depId) {
            $insStmt->bind_param("ss", $projectId, $depId);
            $insStmt->execute();
        }
    }

    $conn->commit();

    echo json_encode(['status' => 'success', 'accepted' => $accepted, 'rejected_cycles' => $rejected]);
} catch (Throwable $e) {
    if (isset($conn)) $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
