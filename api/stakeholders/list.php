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
        SELECT s.id, s.project_id, s.name, s.role_title, s.notes, s.created_at, s.updated_at, u.full_name AS created_by_name
        FROM stakeholders s
        LEFT JOIN users u ON s.created_by = u.id
        WHERE s.project_id = ?
        ORDER BY s.created_at ASC
    ");
    $stmt->bind_param("s", $projectId);
    $stmt->execute();
    $stakeholders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if ($stakeholders) {
        $ids = array_column($stakeholders, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        $docStmt = $conn->prepare("
            SELECT id, stakeholder_id, original_name, size_bytes, uploaded_at
            FROM stakeholder_documents
            WHERE stakeholder_id IN ($placeholders)
            ORDER BY uploaded_at ASC
        ");
        $docStmt->bind_param($types, ...$ids);
        $docStmt->execute();
        $docsByStakeholder = [];
        foreach ($docStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $doc) {
            $docsByStakeholder[$doc['stakeholder_id']][] = $doc;
        }
        foreach ($stakeholders as &$s) {
            $s['documents'] = $docsByStakeholder[$s['id']] ?? [];
        }
        unset($s);
    }

    echo json_encode(['status' => 'success', 'data' => $stakeholders]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
