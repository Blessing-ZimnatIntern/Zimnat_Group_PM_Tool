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
?>
<div class="view-content">
    <h2>Board</h2>
    <div class="board">
        <?php foreach ($stages as $stage): ?>
            <div class="board-col">
                <h3><?= $stageLabels[$stage] ?></h3>
                <?php
                $cards = array_filter($projects, fn($p) => $p['stage'] === $stage);
                if (empty($cards)): ?>
                    <div class="empty-col">No projects</div>
                <?php else: foreach ($cards as $p): ?>
                    <div class="board-card" data-id="<?= $p['id'] ?>">
                        <div class="card-title"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="card-meta">
                            <span><?= htmlspecialchars($p['owner']) ?></span>
                            <span class="status-badge" style="background:<?= getStatusColor($p['status']) ?>"><?= ucfirst($p['status']) ?></span>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
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