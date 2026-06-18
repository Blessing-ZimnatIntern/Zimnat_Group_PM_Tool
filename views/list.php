<?php
require_once __DIR__ . '/../api/bootstrap.php';
use App\Config\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

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
<div class="view-content" style="padding:24px;">
    <h2 style="font-size:20px;font-weight:700;margin-bottom:16px;color:#1a2332;">Project List</h2>
    <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);overflow:hidden;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f9fafb;border-bottom:2px solid #e5e7eb;">
                        <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151;">Project</th>
                        <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151;">Business Unit</th>
                        <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151;">Stage</th>
                        <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151;">Status</th>
                        <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151;">Owner</th>
                        <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151;">Assignee</th>
                        <th style="padding:12px 16px;text-align:right;font-weight:600;color:#374151;">Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projects)): ?>
                        <tr><td colspan="7" style="padding:20px;text-align:center;color:#6b7280;">No projects match the filters.</td></tr>
                    <?php else: foreach ($projects as $p): ?>
                        <tr class="project-row" data-pid="<?= $p['id'] ?>" style="cursor:pointer;border-bottom:1px solid #f3f4f6;transition:background 0.2s, transform 0.1s;">
                            <td style="padding:12px 16px;font-weight:500;"><?= htmlspecialchars($p['name']) ?></td>
                            <td style="padding:12px 16px;color:#4b5563;"><?= htmlspecialchars($p['business_unit']) ?></td>
                            <td style="padding:12px 16px;color:#4b5563;"><?= htmlspecialchars($p['stage']) ?></td>
                            <td style="padding:12px 16px;">
                                <span style="background:<?= getStatusColor($p['effective_status']) ?>;color:#fff;padding:2px 10px;border-radius:5px;font-size:12px;font-weight:600;">
                                    <?= ucfirst($p['effective_status']) ?>
                                </span>
                            </td>
                            <td style="padding:12px 16px;color:#4b5563;"><?= htmlspecialchars($p['owner']) ?></td>
                            <td style="padding:12px 16px;color:#4b5563;"><?= htmlspecialchars($p['assignee_name'] ?? 'Unassigned') ?></td>
                            <td style="padding:12px 16px;text-align:right;font-weight:600;color:#1f2937;"><?= $p['progress'] ?>%</td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<style>
.project-row:hover {
    background: #f3f4f6 !important;
    transform: scale(1.002);
}
</style>
<script>
document.querySelectorAll('.project-row').forEach(row => {
    row.addEventListener('click', function() {
        if (typeof openDrawer === 'function') {
            openDrawer(this.dataset.pid);
        }
    });
});
</script>