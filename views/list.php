<?php
require_once __DIR__ . '/../api/bootstrap.php';
use App\Config\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

// Filters
$assignee = $_GET['assignee'] ?? null;
$status = $_GET['status'] ?? null;
$business_unit = $_GET['business_unit'] ?? null;

$sql = "
    SELECT p.id, p.name, p.stage, p.status, p.owner, p.progress,
           b.name AS business_unit,
           u.full_name AS assignee_name,
           CASE 
               WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue'
               ELSE p.status
           END AS effective_status
    FROM projects p
    JOIN business_units b ON p.business_unit_id = b.id
    LEFT JOIN users u ON p.assignee_id = u.id
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
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<div class="view-content">
    <h2>Project List</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Project</th>
                <th>Business Unit</th>
                <th>Stage</th>
                <th>Status</th>
                <th>Owner</th>
                <th>Assignee</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($projects)): ?>
                <tr><td colspan="7">No projects match the filters.</td></tr>
            <?php else: foreach ($projects as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars($p['business_unit']) ?></td>
                    <td><?= htmlspecialchars($p['stage']) ?></td>
                    <td><span class="status-badge" style="background:<?= getStatusColor($p['effective_status']) ?>"><?= ucfirst($p['effective_status']) ?></span></td>
                    <td><?= htmlspecialchars($p['owner']) ?></td>
                    <td><?= htmlspecialchars($p['assignee_name'] ?? 'Unassigned') ?></td>
                    <td><?= $p['progress'] ?>%</td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php
function getStatusColor($status) {
    $map = [
        'complete' => '#3b82f6',
        'ontrack'  => '#16a34a',
        'atrisk'   => '#f59e0b',
        'overdue'  => '#dc2626',
        'behind'   => '#9ca3af',
    ];
    return $map[$status] ?? '#6b7280';
}
?>