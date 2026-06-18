<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

$user = Auth::requireAuth();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="projects_export_' . date('Y-m-d') . '.csv"');

$db = Database::getInstance();
$conn = $db->getConnection();

// Get filters from query string (same as list.php)
$unit = $_GET['unit'] ?? null;
$status = $_GET['status'] ?? null;
$owner = $_GET['owner'] ?? null;
$assignee = $_GET['assignee'] ?? null;
$search = $_GET['search'] ?? null;

$sql = "
    SELECT 
        p.name AS project_name,
        b.name AS business_unit,
        p.stage,
        p.status,
        p.owner,
        p.assignee,
        p.priority,
        p.gate_due,
        p.completion_due,
        p.progress,
        p.current_update,
        p.next_steps
    FROM projects p
    JOIN business_units b ON p.business_unit_id = b.id
    WHERE p.deleted_at IS NULL
";

$params = [];
$types = '';

if ($unit) {
    $sql .= " AND b.name = ?";
    $params[] = $unit;
    $types .= 's';
}
if ($status) {
    $sql .= " AND p.status = ?";
    $params[] = $status;
    $types .= 's';
}
if ($owner) {
    $sql .= " AND p.owner = ?";
    $params[] = $owner;
    $types .= 's';
}
if ($assignee) {
    $sql .= " AND p.assignee = ?";
    $params[] = $assignee;
    $types .= 's';
}
if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.owner LIKE ? OR p.current_update LIKE ? OR p.next_steps LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'ssss';
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$fp = fopen('php://output', 'w');
// UTF-8 BOM for Excel compatibility
fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($fp, [
    'Project Name', 'Business Unit', 'Stage', 'Status', 'Owner', 'Assignee',
    'Priority', 'Gate Due', 'Completion Due', 'Progress %', 'Current Update', 'Next Steps'
]);

while ($row = $result->fetch_assoc()) {
    fputcsv($fp, $row);
}

fclose($fp);
exit;