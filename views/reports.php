<?php
require_once __DIR__ . '/../api/bootstrap.php';
use App\Config\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

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

$buSql = "
    SELECT b.name, COUNT(*) AS count
    FROM projects p
    JOIN business_units b ON p.business_unit_id = b.id
    WHERE p.deleted_at IS NULL
    GROUP BY b.id
";
$buData = $conn->query($buSql)->fetch_all(MYSQLI_ASSOC);
?>
<div class="view-content" style="padding:24px;">
    <h2 style="font-size:20px;font-weight:700;margin-bottom:16px;color:#1a2332;">Reports</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">
        <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;">
            <h3 style="font-size:16px;font-weight:600;color:#1f2937;margin-bottom:12px;">Portfolio Summary</h3>
            <ul style="list-style:none;padding:0;font-size:14px;">
                <li style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #f3f4f6;"><span>Total Projects</span><span style="font-weight:600;"><?= $stats['total'] ?></span></li>
                <li style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #f3f4f6;"><span>Completed</span><span style="font-weight:600;color:#3b82f6;"><?= $stats['completed'] ?></span></li>
                <li style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #f3f4f6;"><span>On Track</span><span style="font-weight:600;color:#16a34a;"><?= $stats['ontrack'] ?></span></li>
                <li style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #f3f4f6;"><span>At Risk</span><span style="font-weight:600;color:#f59e0b;"><?= $stats['atrisk'] ?></span></li>
                <li style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #f3f4f6;"><span>Behind</span><span style="font-weight:600;color:#9ca3af;"><?= $stats['behind'] ?></span></li>
                <li style="display:flex;justify-content:space-between;padding:4px 0;"><span>Overdue</span><span style="font-weight:600;color:#dc2626;"><?= $stats['overdue'] ?></span></li>
            </ul>
        </div>
        <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;">
            <h3 style="font-size:16px;font-weight:600;color:#1f2937;margin-bottom:12px;">By Business Unit</h3>
            <ul style="list-style:none;padding:0;font-size:14px;">
                <?php foreach ($buData as $bu): ?>
                    <li style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #f3f4f6;">
                        <span><?= htmlspecialchars($bu['name']) ?></span>
                        <span style="font-weight:600;"><?= $bu['count'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>