<?php
require_once __DIR__ . '/../api/bootstrap.php';
use App\Config\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

// Read filters (passed as GET parameters from the AJAX call)
$assignee = $_GET['assignee'] ?? null;
$status = $_GET['status'] ?? null;
$business_unit = $_GET['business_unit'] ?? null;

// Build query with filters (using IDs, not names)
$sql = "
    SELECT p.id, p.name, p.stage, p.status, p.owner,
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

// Stage definitions
$stages = ['initiation', 'planning', 'execution', 'qa', 'uat', 'closure'];
$stageLabels = [
    'initiation' => 'Initiation',
    'planning' => 'Planning',
    'execution' => 'Execution / Development',
    'qa' => 'Quality Assurance',
    'uat' => 'User Acceptance Testing',
    'closure' => 'Project Closure'
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
    <h2 style="font-size:20px;font-weight:700;margin-bottom:16px;color:#1a2332;">Kanban Board</h2>
    <div style="display:flex;gap:16px;overflow-x:auto;align-items:flex-start;padding-bottom:8px;">
        <?php foreach ($stages as $stage): ?>
            <?php
            $cards = array_filter($projects, fn($p) => $p['stage'] === $stage);
            $count = count($cards);
            ?>
            <div style="flex:0 0 280px;background:#f9fafb;border-radius:12px;padding:12px;border:1px solid #e5e7eb;min-height:200px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span style="font-weight:600;font-size:14px;color:#1f2937;"><?= $stageLabels[$stage] ?></span>
                    <span style="background:#e5e7eb;color:#374151;border-radius:10px;padding:0 8px;font-size:12px;font-weight:600;">
                        <?= $count ?>
                    </span>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <?php if (empty($cards)): ?>
                        <div style="color:#9ca3af;text-align:center;padding:20px 0;font-size:13px;border:1px dashed #d1d5db;border-radius:6px;">No projects</div>
                    <?php else: foreach ($cards as $p): ?>
                        <div class="board-card project-row" data-pid="<?= $p['id'] ?>" 
                             style="background:#fff;border-radius:8px;padding:12px;box-shadow:0 1px 3px rgba(0,0,0,0.06);cursor:pointer;transition:all 0.2s ease;border-left:4px solid <?= getStatusColor($p['effective_status']) ?>;">
                            <div style="font-weight:500;font-size:14px;color:#1f2937;margin-bottom:4px;"><?= htmlspecialchars($p['name']) ?></div>
                            <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;">
                                <span><?= htmlspecialchars($p['owner'] ?? 'Unassigned') ?></span>
                                <span style="background:<?= getStatusColor($p['status']) ?>;color:#fff;padding:1px 8px;border-radius:4px;font-weight:600;">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </div>
                            <div style="font-size:11px;color:#9ca3af;margin-top:4px;">
                                <span>Assignee: <?= htmlspecialchars($p['assignee_name'] ?? 'None') ?></span>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.board-card {
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}
.board-card:hover {
    transform: translateY(-2px) scale(1.01);
    box-shadow: 0 8px 25px rgba(0,0,0,0.10);
    background: #fafbfc;
}
.project-row {
    cursor: pointer;
}
.project-row:active {
    transform: scale(0.98);
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