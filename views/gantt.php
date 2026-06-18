<?php
require_once __DIR__ . '/../api/bootstrap.php';
use App\Config\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

$assignee = $_GET['assignee'] ?? null;
$status = $_GET['status'] ?? null;
$business_unit = $_GET['business_unit'] ?? null;

$sql = "
    SELECT p.id, p.name, p.gate_due AS start, p.completion_due AS end,
           p.progress,
           CASE 
               WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue'
               ELSE p.status
           END AS effective_status
    FROM projects p
    JOIN business_units b ON p.business_unit_id = b.id
    WHERE p.deleted_at IS NULL
";
$params = [];
$types = '';

if ($assignee) {
    $sql .= " AND p.assignee_id = ?";
    $params[] = $assignee;
    $types .= 'i';
}
if ($status) {
    $sql .= " AND p.status = ?";
    $params[] = $status;
    $types .= 's';
}
if ($business_unit) {
    $sql .= " AND b.id = ?";
    $params[] = $business_unit;
    $types .= 'i';
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$colorMap = [
    'complete' => '#3b82f6',
    'ontrack' => '#16a34a',
    'atrisk' => '#f59e0b',
    'overdue' => '#dc2626',
    'behind' => '#9ca3af',
];
$ganttData = [];
foreach ($tasks as $t) {
    if (!$t['start'] || !$t['end']) continue;
    $ganttData[] = [
        'id' => $t['id'],
        'name' => $t['name'],
        'start' => $t['start'],
        'end' => $t['end'],
        'progress' => (int)$t['progress'],
        'color' => $colorMap[$t['effective_status']] ?? '#6b7280',
    ];
}
?>
<div class="view-content">
    <h2>Gantt Chart</h2>
    <div id="ganttChart"></div>
</div>
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.5.0/dist/frappe-gantt.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tasks = <?= json_encode($ganttData) ?>;
    var gantt = new Gantt('#ganttChart', tasks, {
        on_click: function(task) {
            if (typeof openDrawer === 'function') {
                openDrawer(task.id);
            }
        }
    });
});
</script>