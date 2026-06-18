<?php
require_once __DIR__ . '/../api/bootstrap.php';
use App\Config\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

$assignee = $_GET['assignee'] ?? null;
$status = $_GET['status'] ?? null;
$business_unit = $_GET['business_unit'] ?? null;

$sql = "
    SELECT p.id, p.name, p.stage, p.status, p.owner,
           u.full_name AS assignee_name
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

$stages = ['initiation', 'planning', 'execution', 'qa', 'uat', 'closure'];
$stageLabels = [
    'initiation' => 'Initiation',
    'planning' => 'Planning',
    'execution' => 'Execution',
    'qa' => 'QA',
    'uat' => 'UAT',
    'closure' => 'Closure'
];
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
    <h2 style="font-size:20px;font-weight:700;margin-bottom:16px;color:#1a2332;">Board</h2>
    <div style="display:flex;gap:16px;overflow-x:auto;align-items:flex-start;">
        <?php foreach ($stages as $stage): ?>
            <div style="flex:0 0 260px;background:#f9fafb;border-radius:12px;padding:12px;border:1px solid #e5e7eb;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="font-weight:600;font-size:14px;color:#1f2937;"><?= $stageLabels[$stage] ?></span>
                    <span style="background:#e5e7eb;color:#374151;border-radius:10px;padding:0 8px;font-size:12px;font-weight:600;">
                        <?= count(array_filter($projects, fn($p) => $p['stage'] === $stage)) ?>
                    </span>
                </div>
                <?php
                $cards = array_filter($projects, fn($p) => $p['stage'] === $stage);
                if (empty($cards)): ?>
                    <div style="color:#9ca3af;text-align:center;padding:20px 0;font-size:13px;">No projects</div>
                <?php else: foreach ($cards as $p): ?>
                    <div class="project-row" data-pid="<?= $p['id'] ?>" style="background:#fff;border-radius:8px;padding:12px;margin-bottom:8px;box-shadow:0 1px 3px rgba(0,0,0,0.06);cursor:pointer;transition:box-shadow 0.2s;">
                        <div style="font-weight:500;font-size:14px;color:#1f2937;margin-bottom:4px;"><?= htmlspecialchars($p['name']) ?></div>
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;">
                            <span><?= htmlspecialchars($p['owner'] ?? 'Unassigned') ?></span>
                            <span style="background:<?= getStatusColor($p['status']) ?>;color:#fff;padding:1px 8px;border-radius:4px;font-weight:600;">
                                <?= ucfirst($p['status']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
document.querySelectorAll('.project-row').forEach(row => {
    row.addEventListener('click', function() {
        if (typeof openDrawer === 'function') {
            openDrawer(this.dataset.pid);
        }
    });
});
</script>