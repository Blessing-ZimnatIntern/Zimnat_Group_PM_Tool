<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireRole('editor');
$data = json_decode(file_get_contents('php://input'), true) ?: [];

$projectId = $data['project_id'] ?? '';
$tagNames = $data['tags'] ?? [];

if (!$projectId || !is_array($tagNames)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'project_id and a tags array are required']);
    exit;
}

// Deterministic color per tag name so the same tag always renders the same color
$palette = ['#dc2626', '#ea580c', '#d97706', '#65a30d', '#16a34a', '#0d9488', '#0891b2', '#2563eb', '#7c3aed', '#c026d3', '#db2777'];
function colorForTag($name, $palette) {
    $hash = 0;
    foreach (str_split($name) as $ch) $hash = ($hash * 31 + ord($ch)) % count($palette);
    return $palette[abs($hash)];
}

// Normalize: trim, drop empties, de-dupe case-insensitively
$clean = [];
foreach ($tagNames as $t) {
    $t = trim((string)$t);
    if ($t === '') continue;
    $key = strtolower($t);
    if (!isset($clean[$key])) $clean[$key] = $t;
}
$tagNames = array_values($clean);

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

    $conn->begin_transaction();

    $tagIds = [];
    foreach ($tagNames as $name) {
        $selStmt = $conn->prepare("SELECT id FROM tags WHERE name = ?");
        $selStmt->bind_param("s", $name);
        $selStmt->execute();
        $existing = $selStmt->get_result()->fetch_assoc();
        if ($existing) {
            $tagIds[] = (int)$existing['id'];
        } else {
            $color = colorForTag($name, $palette);
            $insStmt = $conn->prepare("INSERT INTO tags (name, color) VALUES (?, ?)");
            $insStmt->bind_param("ss", $name, $color);
            $insStmt->execute();
            $tagIds[] = (int)$insStmt->insert_id;
        }
    }

    $delStmt = $conn->prepare("DELETE FROM project_tags WHERE project_id = ?");
    $delStmt->bind_param("s", $projectId);
    $delStmt->execute();

    if (!empty($tagIds)) {
        $insLinkStmt = $conn->prepare("INSERT INTO project_tags (project_id, tag_id) VALUES (?, ?)");
        foreach ($tagIds as $tagId) {
            $insLinkStmt->bind_param("si", $projectId, $tagId);
            $insLinkStmt->execute();
        }
    }

    $conn->commit();

    $tagStmt = $conn->prepare("
        SELECT t.id, t.name, t.color FROM tags t
        JOIN project_tags pt ON pt.tag_id = t.id
        WHERE pt.project_id = ?
        ORDER BY t.name
    ");
    $tagStmt->bind_param("s", $projectId);
    $tagStmt->execute();

    echo json_encode(['status' => 'success', 'tags' => $tagStmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
} catch (Throwable $e) {
    if (isset($conn)) $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
