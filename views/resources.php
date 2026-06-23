<?php
require_once __DIR__ . '/../api/bootstrap.php';
use App\Config\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

$cellsStmt = $conn->query("
    SELECT pa.project_id, pa.user_id, pa.allocation_pct,
           u.full_name AS user_name,
           p.name AS project_name
    FROM project_assignees pa
    JOIN users u ON pa.user_id = u.id
    JOIN projects p ON pa.project_id = p.id
    WHERE p.deleted_at IS NULL
    ORDER BY u.full_name, p.name
");
$cells = $cellsStmt->fetch_all(MYSQLI_ASSOC);

$users = [];
$projects = [];
foreach ($cells as $c) {
    $users[$c['user_id']] = $c['user_name'];
    $projects[$c['project_id']] = $c['project_name'];
}

$totalsStmt = $conn->query("
    SELECT pa.user_id, u.full_name, SUM(pa.allocation_pct) AS total_pct
    FROM project_assignees pa
    JOIN users u ON pa.user_id = u.id
    JOIN projects p ON pa.project_id = p.id
    WHERE p.deleted_at IS NULL
    GROUP BY pa.user_id, u.full_name
");
$totalsByUser = [];
while ($row = $totalsStmt->fetch_assoc()) {
    $totalsByUser[$row['user_id']] = (int)$row['total_pct'];
}

function allocationColor($pct) {
    if ($pct == 0) return '#f3f4f6';
    if ($pct <= 25) return '#bbf7d0';
    if ($pct <= 50) return '#86efac';
    if ($pct <= 75) return '#fde68a';
    if ($pct <= 100) return '#fca5a5';
    return '#dc2626';
}
?>
<div class="view-content" style="padding:24px;">
    <h2 style="font-size:20px;font-weight:700;margin-bottom:6px;color:#1a2332;">Resource Heatmap</h2>
    <p style="color:#6b7280;font-size:13px;margin-bottom:16px;">Allocation % per team member, per project. Darker cells mean heavier load; a red user-total means they're over 100% allocated across all their projects.</p>

    <?php if (empty($users)): ?>
        <div class="empty-state">No projects have assignees yet. Assign team members from a project's "Assignees" tab.</div>
    <?php else: ?>
    <div style="overflow:auto;background:#fff;border-radius:12px;border:1px solid #e5e7eb;">
        <table style="border-collapse:collapse;font-size:12.5px;min-width:600px;">
            <thead>
                <tr>
                    <th style="position:sticky;left:0;background:#f9fafb;padding:10px 14px;text-align:left;border-bottom:2px solid #e5e7eb;border-right:1px solid #e5e7eb;">Project</th>
                    <?php foreach ($users as $uid => $uname): ?>
                        <th style="padding:10px 12px;text-align:center;border-bottom:2px solid #e5e7eb;min-width:100px;">
                            <?= htmlspecialchars($uname) ?>
                            <div style="font-size:11px;font-weight:700;color:<?= ($totalsByUser[$uid] ?? 0) > 100 ? '#dc2626' : '#6b7280' ?>;">
                                <?= $totalsByUser[$uid] ?? 0 ?>% total
                            </div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $pid => $pname): ?>
                <tr>
                    <td class="heatmap-project-cell" data-pid="<?= htmlspecialchars($pid) ?>" style="position:sticky;left:0;background:#fff;padding:8px 14px;border-right:1px solid #e5e7eb;border-bottom:1px solid #f3f4f6;font-weight:500;cursor:pointer;white-space:nowrap;">
                        <?= htmlspecialchars($pname) ?>
                    </td>
                    <?php foreach ($users as $uid => $uname):
                        $cell = null;
                        foreach ($cells as $c) { if ($c['project_id'] === $pid && (int)$c['user_id'] === (int)$uid) { $cell = $c; break; } }
                        $pct = $cell ? (int)$cell['allocation_pct'] : 0;
                    ?>
                        <td class="heatmap-cell" data-pid="<?= htmlspecialchars($pid) ?>" style="text-align:center;padding:8px 12px;border-bottom:1px solid #f3f4f6;background:<?= allocationColor($pct) ?>;cursor:<?= $pct ? 'pointer' : 'default' ?>;">
                            <?= $pct ? $pct . '%' : '' ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<script>
document.querySelectorAll('.heatmap-cell, .heatmap-project-cell').forEach(function(el) {
    el.addEventListener('click', function() {
        if (!this.dataset.pid) return;
        if (typeof openDrawer === 'function') openDrawer(this.dataset.pid);
    });
});
</script>
