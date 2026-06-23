<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;
use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/../helpers/ExecutiveSummary.php';

$user = Auth::requireAuth();

$dateFrom = $_GET['from'] ?? '';
$dateTo = $_GET['to'] ?? '';
$unit = $_GET['unit'] ?? null;
$status = $_GET['status'] ?? null;
$ownerId = $_GET['owner_id'] ?? null;
$assignee = $_GET['assignee'] ?? null;
$search = $_GET['search'] ?? null;

$db = Database::getInstance();
$conn = $db->getConnection();

$summaryUnitId = null;
if ($unit) {
    $unitStmt = $conn->prepare("SELECT id FROM business_units WHERE name = ?");
    $unitStmt->bind_param("s", $unit);
    $unitStmt->execute();
    $unitRow = $unitStmt->get_result()->fetch_assoc();
    if ($unitRow) $summaryUnitId = (int)$unitRow['id'];
}
$executiveSummary = buildExecutiveSummary($conn, $summaryUnitId)['summary'];

$sql = "
    SELECT
        p.id, p.name, p.stage, p.owner, p.progress, p.gate_due, p.completion_due,
        p.budget, p.actual_cost, p.currency,
        b.name AS business_unit,
        u.full_name AS assignee_name,
        CASE WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue' ELSE p.status END AS effective_status
    FROM projects p
    JOIN business_units b ON p.business_unit_id = b.id
    LEFT JOIN users u ON p.assignee_id = u.id
    WHERE p.deleted_at IS NULL
";
$params = [];
$types = '';
if ($dateFrom) { $sql .= " AND p.completion_due >= ?"; $params[] = $dateFrom; $types .= 's'; }
if ($dateTo) { $sql .= " AND p.completion_due <= ?"; $params[] = $dateTo; $types .= 's'; }
if ($unit) { $sql .= " AND b.name = ?"; $params[] = $unit; $types .= 's'; }
if ($status) { $sql .= " AND p.status = ?"; $params[] = $status; $types .= 's'; }
if ($ownerId) { $sql .= " AND p.owner_id = ?"; $params[] = (int)$ownerId; $types .= 'i'; }
if ($assignee) { $sql .= " AND p.assignee_id = ?"; $params[] = (int)$assignee; $types .= 'i'; }
if ($search) { $sql .= " AND p.name LIKE ?"; $params[] = '%' . $search . '%'; $types .= 's'; }
$sql .= " ORDER BY b.sort_order, p.completion_due ASC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$statusColors = ['complete' => '#3b82f6', 'ontrack' => '#16a34a', 'atrisk' => '#f59e0b', 'overdue' => '#dc2626', 'behind' => '#9ca3af'];

$buStats = [];
$counts = ['ontrack' => 0, 'atrisk' => 0, 'behind' => 0, 'overdue' => 0, 'complete' => 0];
$totalProgress = 0;
$totalBudget = 0;
$totalActual = 0;
foreach ($projects as $p) {
    $bu = $p['business_unit'];
    if (!isset($buStats[$bu])) $buStats[$bu] = ['total' => 0, 'completed' => 0, 'overdue' => 0, 'budget' => 0, 'actual' => 0];
    $buStats[$bu]['total']++;
    $es = $p['effective_status'];
    if (isset($counts[$es])) $counts[$es]++;
    if ($es === 'complete') $buStats[$bu]['completed']++;
    if ($es === 'overdue') $buStats[$bu]['overdue']++;
    $totalProgress += (int)$p['progress'];
    $buStats[$bu]['budget'] += (float)($p['budget'] ?? 0);
    $buStats[$bu]['actual'] += (float)($p['actual_cost'] ?? 0);
    $totalBudget += (float)($p['budget'] ?? 0);
    $totalActual += (float)($p['actual_cost'] ?? 0);
}
$avgProgress = count($projects) ? round($totalProgress / count($projects)) : 0;
$rangeLabel = ($dateFrom || $dateTo) ? htmlspecialchars(($dateFrom ?: 'any') . ' to ' . ($dateTo ?: 'any')) : 'All dates';

ob_start();
?>
<html>
<head>
<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1a2332; }
    h1 { font-size: 20px; color: #1a4a7a; margin-bottom: 2px; }
    .meta { font-size: 10px; color: #6b7280; margin-bottom: 16px; }
    .stat-row { width: 100%; margin-bottom: 16px; }
    .stat-row td { text-align: center; border: 1px solid #e5e7eb; padding: 8px; }
    .stat-row .n { font-size: 18px; font-weight: bold; display: block; }
    table.data { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    table.data th { background: #f9fafb; border: 1px solid #e5e7eb; padding: 5px 8px; text-align: left; font-size: 10px; }
    table.data td { border: 1px solid #e5e7eb; padding: 5px 8px; }
    .pill { color: #fff; padding: 2px 6px; border-radius: 4px; font-size: 9px; }
    h2 { font-size: 14px; margin-top: 20px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
    .exec-summary { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; font-size: 11px; line-height: 1.6; }
</style>
</head>
<body>
    <h1>Project Management Report</h1>
    <div class="meta">Generated: <?= date('Y-m-d H:i') ?> &middot; Completion date range: <?= $rangeLabel ?></div>

    <h2 style="margin-top:0;">Executive Summary</h2>
    <div class="exec-summary"><?= htmlspecialchars($executiveSummary) ?></div>

    <table class="stat-row">
        <tr>
            <td><span class="n"><?= count($projects) ?></span>Total</td>
            <td><span class="n" style="color:<?= $statusColors['ontrack'] ?>"><?= $counts['ontrack'] ?></span>On Track</td>
            <td><span class="n" style="color:<?= $statusColors['atrisk'] ?>"><?= $counts['atrisk'] ?></span>At Risk</td>
            <td><span class="n" style="color:<?= $statusColors['overdue'] ?>"><?= $counts['overdue'] ?></span>Overdue</td>
            <td><span class="n"><?= $avgProgress ?>%</span>Avg Progress</td>
        </tr>
    </table>

    <h2>Financial Summary</h2>
    <table class="stat-row">
        <tr>
            <td><span class="n">$<?= number_format($totalBudget, 2) ?></span>Total Budget</td>
            <td><span class="n" style="color:<?= $totalActual > $totalBudget && $totalBudget > 0 ? $statusColors['overdue'] : $statusColors['ontrack'] ?>">$<?= number_format($totalActual, 2) ?></span>Total Actual Cost</td>
            <td><span class="n">$<?= number_format($totalBudget - $totalActual, 2) ?></span>Variance</td>
        </tr>
    </table>
    <table class="data">
        <tr><th>Business Unit</th><th>Budget</th><th>Actual Cost</th><th>Variance</th></tr>
        <?php foreach ($buStats as $bu => $s): $buOverBudget = $s['budget'] > 0 && $s['actual'] > $s['budget']; ?>
        <tr><td><?= htmlspecialchars($bu) ?></td><td>$<?= number_format($s['budget'], 2) ?></td><td<?= $buOverBudget ? ' style="color:' . $statusColors['overdue'] . ';font-weight:bold;"' : '' ?>>$<?= number_format($s['actual'], 2) ?></td><td>$<?= number_format($s['budget'] - $s['actual'], 2) ?></td></tr>
        <?php endforeach; ?>
    </table>

    <h2>Business Unit Ranking</h2>
    <table class="data">
        <tr><th>Business Unit</th><th>Total</th><th>Completed</th><th>Overdue</th></tr>
        <?php foreach ($buStats as $bu => $s): ?>
        <tr><td><?= htmlspecialchars($bu) ?></td><td><?= $s['total'] ?></td><td><?= $s['completed'] ?></td><td><?= $s['overdue'] ?></td></tr>
        <?php endforeach; ?>
    </table>

    <h2>Project Detail</h2>
    <table class="data">
        <tr><th>Project</th><th>Business Unit</th><th>Stage</th><th>Status</th><th>Owner</th><th>Assignee</th><th>Progress</th><th>Budget</th><th>Actual Cost</th><th>Completion Due</th></tr>
        <?php foreach ($projects as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['business_unit']) ?></td>
            <td><?= htmlspecialchars(ucfirst($p['stage'])) ?></td>
            <td><span class="pill" style="background:<?= $statusColors[$p['effective_status']] ?? '#6b7280' ?>"><?= htmlspecialchars(ucfirst($p['effective_status'])) ?></span></td>
            <td><?= htmlspecialchars($p['owner'] ?? '-') ?></td>
            <td><?= htmlspecialchars($p['assignee_name'] ?? 'Unassigned') ?></td>
            <td><?= (int)$p['progress'] ?>%</td>
            <?php $projectOverBudget = $p['budget'] !== null && $p['actual_cost'] !== null && (float)$p['actual_cost'] > (float)$p['budget']; ?>
            <td><?= $p['budget'] !== null ? number_format((float)$p['budget'], 2) . ' ' . htmlspecialchars($p['currency']) : '-' ?></td>
            <td<?= $projectOverBudget ? ' style="color:' . $statusColors['overdue'] . ';font-weight:bold;"' : '' ?>><?= $p['actual_cost'] !== null ? number_format((float)$p['actual_cost'], 2) . ' ' . htmlspecialchars($p['currency']) : '-' ?></td>
            <td><?= htmlspecialchars($p['completion_due'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($projects)): ?>
        <tr><td colspan="10" style="text-align:center;color:#9ca3af;">No projects match this report's filters.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', false);
$dompdf = new Dompdf($options);
$dompdf->setPaper('A4', 'landscape');
$dompdf->loadHtml($html);
$dompdf->render();

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="project_report_' . date('Y-m-d') . '.pdf"');
echo $dompdf->output();
exit;
