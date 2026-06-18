<?php
require_once __DIR__ . '/../api/bootstrap.php';
use App\Config\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

// Aggregate stats
$statsSql = "
    SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'complete' THEN 1 ELSE 0 END) AS completed,
        SUM(CASE WHEN status = 'ontrack' THEN 1 ELSE 0 END) AS ontrack,
        SUM(CASE WHEN status = 'atrisk' THEN 1 ELSE 0 END) AS atrisk,
        SUM(CASE WHEN status = 'behind' THEN 1 ELSE 0 END) AS behind,
        SUM(CASE WHEN status != 'complete' AND completion_due < CURDATE() THEN 1 ELSE 0 END) AS overdue
    FROM projects
    WHERE deleted_at IS NULL
";
$stats = $conn->query($statsSql)->fetch_assoc();

// Per business unit
$buSql = "
    SELECT b.name, COUNT(*) AS count
    FROM projects p
    JOIN business_units b ON p.business_unit_id = b.id
    WHERE p.deleted_at IS NULL
    GROUP BY b.id
";
$buData = $conn->query($buSql)->fetch_all(MYSQLI_ASSOC);
?>
<div class="view-content">
    <h2>Reports</h2>
    <div class="report-grid">
        <div class="report-card">
            <h3>Portfolio Summary</h3>
            <ul>
                <li>Total Projects: <?= $stats['total'] ?></li>
                <li>Completed: <?= $stats['completed'] ?></li>
                <li>On Track: <?= $stats['ontrack'] ?></li>
                <li>At Risk: <?= $stats['atrisk'] ?></li>
                <li>Behind: <?= $stats['behind'] ?></li>
                <li>Overdue: <?= $stats['overdue'] ?></li>
            </ul>
        </div>
        <div class="report-card">
            <h3>By Business Unit</h3>
            <ul>
                <?php foreach ($buData as $bu): ?>
                    <li><?= htmlspecialchars($bu['name']) ?>: <?= $bu['count'] ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>