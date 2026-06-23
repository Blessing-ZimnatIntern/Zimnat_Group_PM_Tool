<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

$user = Auth::requireAuth();

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="projects_export_' . date('Y-m-d') . '.txt"');

$db = Database::getInstance();
$conn = $db->getConnection();

$unit = $_GET['unit'] ?? null;
$status = $_GET['status'] ?? null;
$owner = $_GET['owner'] ?? null;
$ownerId = $_GET['owner_id'] ?? null;
$assignee = $_GET['assignee'] ?? null;
$search = $_GET['search'] ?? null;
$dateFrom = $_GET['from'] ?? null;
$dateTo = $_GET['to'] ?? null;

$sql = "
    SELECT
        p.name AS project_name,
        b.name AS business_unit,
        p.stage,
        p.status,
        p.owner,
        u.full_name AS assignee,
        p.priority,
        p.gate_due,
        p.completion_due,
        p.progress,
        p.budget,
        p.actual_cost,
        p.currency,
        p.current_update,
        p.next_steps
    FROM projects p
    JOIN business_units b ON p.business_unit_id = b.id
    LEFT JOIN users u ON p.assignee_id = u.id
    WHERE p.deleted_at IS NULL
";

$params = [];
$types = '';

if ($unit) { $sql .= " AND b.name = ?"; $params[] = $unit; $types .= 's'; }
if ($status) { $sql .= " AND p.status = ?"; $params[] = $status; $types .= 's'; }
if ($owner) { $sql .= " AND p.owner = ?"; $params[] = $owner; $types .= 's'; }
if ($ownerId) { $sql .= " AND p.owner_id = ?"; $params[] = (int)$ownerId; $types .= 'i'; }
if ($assignee) { $sql .= " AND p.assignee_id = ?"; $params[] = (int)$assignee; $types .= 'i'; }
if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.owner LIKE ? OR p.current_update LIKE ? OR p.next_steps LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'ssss';
}
if ($dateFrom) { $sql .= " AND p.completion_due >= ?"; $params[] = $dateFrom; $types .= 's'; }
if ($dateTo) { $sql .= " AND p.completion_due <= ?"; $params[] = $dateTo; $types .= 's'; }
$sql .= " ORDER BY b.sort_order, p.completion_due ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

echo "Project Status Report\n";
echo "Generated: " . date('Y-m-d H:i') . "\n";
echo str_repeat('=', 60) . "\n\n";

while ($row = $result->fetch_assoc()) {
    echo $row['project_name'] . " (" . $row['business_unit'] . ")\n";
    echo "  Stage:           " . ucfirst($row['stage']) . "\n";
    echo "  Status:          " . ucfirst($row['status']) . "\n";
    echo "  Owner:           " . ($row['owner'] ?: '-') . "\n";
    echo "  Assignee:        " . ($row['assignee'] ?: 'Unassigned') . "\n";
    echo "  Priority:        " . ucfirst($row['priority']) . "\n";
    echo "  Stage gate due:  " . ($row['gate_due'] ?: '-') . "\n";
    echo "  Completion due:  " . ($row['completion_due'] ?: '-') . "\n";
    echo "  Progress:        " . $row['progress'] . "%\n";
    if ($row['budget'] !== null) echo "  Budget:          " . number_format((float)$row['budget'], 2) . " " . $row['currency'] . "\n";
    if ($row['actual_cost'] !== null) echo "  Actual cost:     " . number_format((float)$row['actual_cost'], 2) . " " . $row['currency'] . "\n";
    if (!empty($row['current_update'])) echo "  Current update:  " . $row['current_update'] . "\n";
    if (!empty($row['next_steps'])) echo "  Next steps:      " . $row['next_steps'] . "\n";
    echo str_repeat('-', 60) . "\n";
}
exit;
