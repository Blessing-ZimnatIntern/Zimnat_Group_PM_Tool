<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();

try {
    $conn = Database::getInstance()->getConnection();

    // Check for columns
    $colStmt = $conn->prepare("SELECT COUNT(*) AS total FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'deleted_at'");
    $colStmt->execute();
    $hasDeletedAt = ((int)$colStmt->get_result()->fetch_assoc()['total']) > 0;

    $assigneeStmt = $conn->prepare("SELECT COUNT(*) AS total FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'projects' AND COLUMN_NAME = 'assignee_id'");
    $assigneeStmt->execute();
    $hasAssigneeId = ((int)$assigneeStmt->get_result()->fetch_assoc()['total']) > 0;

    // Filters
    $unitFilter = $_GET['unit'] ?? null;
    $statusFilter = $_GET['status'] ?? null;
    $ownerFilter = $_GET['owner'] ?? null;
    $ownerIdFilter = $_GET['owner_id'] ?? null;
    $assigneeFilter = $_GET['assignee'] ?? null; // now expects INT
    $search = $_GET['search'] ?? null;

    $sql = "
        SELECT
            p.*,
            ou.full_name AS owner_name,
            b.id AS business_unit_id,
            b.name AS business_unit_name,
            b.color AS business_unit_color,
            CASE
                WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue'
                ELSE p.status
            END AS effective_status,
            CASE
                WHEN p.gate_due < CURDATE() AND p.status != 'complete' THEN 1
                ELSE 0
            END AS is_gate_overdue
        FROM projects p
        JOIN business_units b ON p.business_unit_id = b.id
        LEFT JOIN users ou ON p.owner_id = ou.id
    ";

    $where = [];
    $params = [];
    $types = '';

    if ($hasDeletedAt) $where[] = "p.deleted_at IS NULL";
    if ($unitFilter) { $where[] = "b.name = ?"; $params[] = $unitFilter; $types .= 's'; }
    if ($statusFilter) { $where[] = "p.status = ?"; $params[] = $statusFilter; $types .= 's'; }
    if ($ownerFilter) { $where[] = "p.owner = ?"; $params[] = $ownerFilter; $types .= 's'; }
    if ($ownerIdFilter) { $where[] = "p.owner_id = ?"; $params[] = (int)$ownerIdFilter; $types .= 'i'; }
    if ($assigneeFilter && $hasAssigneeId) { $where[] = "p.assignee_id = ?"; $params[] = (int)$assigneeFilter; $types .= 'i'; }
    if ($search) {
        $like = '%' . $search . '%';
        $where[] = "(p.name LIKE ? OR p.owner LIKE ? OR p.current_update LIKE ? OR p.next_steps LIKE ?)";
        $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
        $types .= 'ssss';
    }

    if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
    $sql .= " ORDER BY b.sort_order, p.completion_due ASC, p.created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $projectResult = $stmt->get_result();

    $projects = [];
    $projectIds = [];
    while ($row = $projectResult->fetch_assoc()) {
        $row['governance'] = [];
        $row['tags'] = [];
        $row['stakeholders'] = [];
        $projects[$row['id']] = $row;
        $projectIds[] = $row['id'];
    }

    // Fetch governance
    if ($projectIds) {
        $placeholders = implode(',', array_fill(0, count($projectIds), '?'));
        $types = str_repeat('s', count($projectIds));
        $govStmt = $conn->prepare("SELECT project_id, item_key, item_label, stage_gate, status FROM governance_items WHERE project_id IN ($placeholders)");
        $govStmt->bind_param($types, ...$projectIds);
        $govStmt->execute();
        $govResult = $govStmt->get_result();
        while ($row = $govResult->fetch_assoc()) {
            if (isset($projects[$row['project_id']])) {
                $projects[$row['project_id']]['governance'][] = $row;
            }
        }

        $tagStmt = $conn->prepare("
            SELECT pt.project_id, t.id, t.name, t.color
            FROM project_tags pt JOIN tags t ON pt.tag_id = t.id
            WHERE pt.project_id IN ($placeholders)
        ");
        $tagStmt->bind_param($types, ...$projectIds);
        $tagStmt->execute();
        $tagResult = $tagStmt->get_result();
        while ($row = $tagResult->fetch_assoc()) {
            if (isset($projects[$row['project_id']])) {
                $projects[$row['project_id']]['tags'][] = ['id' => $row['id'], 'name' => $row['name'], 'color' => $row['color']];
            }
        }

        $stakeholderStmt = $conn->prepare("SELECT id, project_id, name FROM stakeholders WHERE project_id IN ($placeholders)");
        $stakeholderStmt->bind_param($types, ...$projectIds);
        $stakeholderStmt->execute();
        $stakeholderResult = $stakeholderStmt->get_result();
        while ($row = $stakeholderResult->fetch_assoc()) {
            if (isset($projects[$row['project_id']])) {
                $projects[$row['project_id']]['stakeholders'][] = ['id' => $row['id'], 'name' => $row['name']];
            }
        }
    }

    // Get units
    $unitResult = $conn->query("SELECT id, name, color, sort_order FROM business_units ORDER BY sort_order, name");
    $units = [];
    while ($row = $unitResult->fetch_assoc()) {
        $row['slug'] = strtolower(preg_replace('/[^a-z0-9]+/i', '', $row['name']));
        $units[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'business_units' => $units,
            'projects' => array_values($projects),
            'user' => $user,
            'has_assignee' => $hasAssigneeId
        ]
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}