<?php
require_once 'api/bootstrap.php';

use App\Config\Database;

$token = $_GET['token'] ?? '';

if (!$token) {
    die('Missing token.');
}

$db = Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    SELECT * FROM shared_reports 
    WHERE token = ? AND (expires_at IS NULL OR expires_at > NOW())
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$share = $result->fetch_assoc();

if (!$share) {
    die('Invalid or expired share link.');
}

$filters = json_decode($share['filters'] ?? '', true) ?: [];
$unitFilter = $filters['business_unit'] ?? null;
$statusFilter = $filters['status'] ?? null;
$ownerIdFilter = $filters['owner_id'] ?? null;
$assigneeFilter = $filters['assignee'] ?? null;
$searchFilter = $filters['search'] ?? null;

$sql = "
    SELECT p.id, p.name, p.stage, p.owner, p.progress, p.gate_due, p.completion_due,
           b.name AS business_unit, b.color AS business_unit_color,
           u.full_name AS assignee_name,
           CASE WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue' ELSE p.status END AS effective_status
    FROM projects p
    JOIN business_units b ON p.business_unit_id = b.id
    LEFT JOIN users u ON p.assignee_id = u.id
    WHERE p.deleted_at IS NULL
";
$params = [];
$types = '';
if ($unitFilter) { $sql .= " AND b.id = ?"; $params[] = (int)$unitFilter; $types .= 'i'; }
if ($statusFilter) { $sql .= " AND p.status = ?"; $params[] = $statusFilter; $types .= 's'; }
if ($ownerIdFilter) { $sql .= " AND p.owner_id = ?"; $params[] = (int)$ownerIdFilter; $types .= 'i'; }
if ($assigneeFilter) { $sql .= " AND p.assignee_id = ?"; $params[] = (int)$assigneeFilter; $types .= 'i'; }
if ($searchFilter) { $sql .= " AND p.name LIKE ?"; $params[] = '%' . $searchFilter . '%'; $types .= 's'; }
$sql .= " ORDER BY b.sort_order, p.completion_due ASC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$statusColors = ['complete' => '#3b82f6', 'ontrack' => '#16a34a', 'atrisk' => '#f59e0b', 'overdue' => '#dc2626', 'behind' => '#9ca3af'];
$counts = ['ontrack' => 0, 'atrisk' => 0, 'behind' => 0, 'overdue' => 0, 'complete' => 0];
$totalProgress = 0;
foreach ($projects as $p) {
    if (isset($counts[$p['effective_status']])) $counts[$p['effective_status']]++;
    $totalProgress += (int)$p['progress'];
}
$avgProgress = count($projects) ? round($totalProgress / count($projects)) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Shared Report – Zimnat</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; padding: 20px; color: #1a2332; }
        .container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; }
        h1 { color: #1a4a7a; margin-bottom: 4px; }
        .note { color: #6b7a8f; font-size: 13px; margin-bottom: 24px; }
        .stat-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 14px; margin-bottom: 24px; }
        .scard { border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; text-align: center; }
        .scard .n { font-size: 24px; font-weight: 800; }
        .scard .l { font-size: 12px; color: #6b7a8f; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { text-align: left; background: #f9fafb; border-bottom: 2px solid #e5e7eb; padding: 10px 12px; }
        td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; }
        .pill { color: #fff; padding: 2px 10px; border-radius: 5px; font-size: 12px; font-weight: 600; }
        @media print { body { background: #fff; padding: 0; } .container { box-shadow: none; } }
    </style>
</head>
<body>
<div class="container">
    <h1>Shared Project Report</h1>
    <p class="note">Read-only view &middot; Expires: <?= htmlspecialchars($share['expires_at'] ?? 'Never') ?> &middot; Generated <?= date('Y-m-d H:i') ?></p>

    <div class="stat-row">
        <div class="scard"><div class="n"><?= count($projects) ?></div><div class="l">Total Projects</div></div>
        <div class="scard"><div class="n" style="color:<?= $statusColors['ontrack'] ?>"><?= $counts['ontrack'] ?></div><div class="l">On Track</div></div>
        <div class="scard"><div class="n" style="color:<?= $statusColors['atrisk'] ?>"><?= $counts['atrisk'] ?></div><div class="l">At Risk</div></div>
        <div class="scard"><div class="n" style="color:<?= $statusColors['overdue'] ?>"><?= $counts['overdue'] ?></div><div class="l">Overdue</div></div>
        <div class="scard"><div class="n"><?= $avgProgress ?>%</div><div class="l">Avg Progress</div></div>
    </div>

    <table>
        <thead>
            <tr><th>Project</th><th>Business Unit</th><th>Stage</th><th>Status</th><th>Owner</th><th>Assignee</th><th>Progress</th><th>Completion Due</th></tr>
        </thead>
        <tbody>
            <?php if (empty($projects)): ?>
                <tr><td colspan="8" style="text-align:center;color:#9aa8b9;">No projects match this report's filters.</td></tr>
            <?php else: foreach ($projects as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['name']) ?></td>
                    <td><?= htmlspecialchars($p['business_unit']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($p['stage'])) ?></td>
                    <td><span class="pill" style="background:<?= $statusColors[$p['effective_status']] ?? '#6b7280' ?>"><?= htmlspecialchars(ucfirst($p['effective_status'])) ?></span></td>
                    <td><?= htmlspecialchars($p['owner'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['assignee_name'] ?? 'Unassigned') ?></td>
                    <td><?= (int)$p['progress'] ?>%</td>
                    <td><?= htmlspecialchars($p['completion_due'] ?? '—') ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>