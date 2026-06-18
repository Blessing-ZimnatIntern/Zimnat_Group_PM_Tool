<?php
require_once __DIR__ . '/../api/bootstrap.php';
use App\Config\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

$assignee = $_GET['assignee'] ?? null;
$status = $_GET['status'] ?? null;
$business_unit = $_GET['business_unit'] ?? null;

$sql = "
    SELECT p.id, p.name AS title, p.gate_due AS start, p.completion_due AS end,
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
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$colorMap = [
    'complete' => '#3b82f6',
    'ontrack' => '#16a34a',
    'atrisk' => '#f59e0b',
    'overdue' => '#dc2626',
    'behind' => '#9ca3af',
];
$eventsJson = [];
foreach ($events as $e) {
    if (!$e['start'] || !$e['end']) continue;
    $eventsJson[] = [
        'id' => $e['id'],
        'title' => $e['title'],
        'start' => $e['start'],
        'end' => $e['end'],
        'color' => $colorMap[$e['effective_status']] ?? '#6b7280',
        'textColor' => '#fff',
    ];
}
?>
<div class="view-content">
    <h2>Calendar</h2>
    <div id="calendarView"></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendarView');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: <?= json_encode($eventsJson) ?>,
        eventClick: function(info) {
            if (typeof openDrawer === 'function') {
                openDrawer(info.event.id);
            }
        }
    });
    calendar.render();
});
</script>