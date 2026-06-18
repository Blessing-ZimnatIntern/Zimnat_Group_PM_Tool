<?php
require_once __DIR__ . '/../api/bootstrap.php';
use App\Config\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

// Fetch all projects with business unit and assignee
$sql = "
    SELECT 
        p.id, p.name, p.status, p.owner, p.assignee_id, p.progress,
        p.gate_due, p.completion_due,
        b.name AS business_unit,
        u.full_name AS assignee_name,
        DATEDIFF(p.completion_due, p.created_at) AS duration,
        CASE 
            WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue'
            ELSE p.status
        END AS effective_status
    FROM projects p
    JOIN business_units b ON p.business_unit_id = b.id
    LEFT JOIN users u ON p.assignee_id = u.id
    WHERE p.deleted_at IS NULL
";
$projects = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// 1. Business Unit Ranking
$buStats = [];
foreach ($projects as $p) {
    $bu = $p['business_unit'];
    if (!isset($buStats[$bu])) {
        $buStats[$bu] = ['total'=>0, 'completed'=>0, 'overdue'=>0, 'behind'=>0, 'atrisk'=>0, 'ontrack'=>0];
    }
    $buStats[$bu]['total']++;
    $status = $p['effective_status'];
    if ($status === 'complete') $buStats[$bu]['completed']++;
    elseif ($status === 'overdue') $buStats[$bu]['overdue']++;
    elseif ($status === 'behind') $buStats[$bu]['behind']++;
    elseif ($status === 'atrisk') $buStats[$bu]['atrisk']++;
    else $buStats[$bu]['ontrack']++;
}

// 2. Assignee Ranking
$assigneeStats = [];
foreach ($projects as $p) {
    $assignee = $p['assignee_name'] ?? 'Unassigned';
    if (!isset($assigneeStats[$assignee])) {
        $assigneeStats[$assignee] = ['total'=>0, 'completed'=>0, 'overdue'=>0, 'atrisk'=>0, 'ontrack'=>0];
    }
    $assigneeStats[$assignee]['total']++;
    $status = $p['effective_status'];
    if ($status === 'complete') $assigneeStats[$assignee]['completed']++;
    elseif ($status === 'overdue') $assigneeStats[$assignee]['overdue']++;
    elseif ($status === 'atrisk') $assigneeStats[$assignee]['atrisk']++;
    elseif ($status === 'ontrack') $assigneeStats[$assignee]['ontrack']++;
}

// 3. Timeline Analysis
$durations = array_filter(array_column($projects, 'duration'), function($d) { return $d !== null && $d > 0; });
sort($durations);
$shortest = $durations[0] ?? 0;
$longest = end($durations) ?? 0;
$avg = count($durations) ? round(array_sum($durations)/count($durations)) : 0;

$shortestProjects = array_filter($projects, function($p) use ($shortest) { return $p['duration'] == $shortest && $shortest > 0; });
$longestProjects = array_filter($projects, function($p) use ($longest) { return $p['duration'] == $longest && $longest > 0; });
?>
<div class="view-content" style="padding:24px;max-width:1400px;margin:0 auto;">

    <h1 style="font-size:28px;font-weight:800;color:#1a4a7a;margin-bottom:8px;">Project Management Report</h1>
    <p style="color:#6b7280;margin-bottom:24px;">Generated: <?= date('Y-m-d H:i') ?></p>

    <!-- Page 1: Business Unit Ranking -->
    <section style="margin-bottom:48px;page-break-after:always;">
        <h2 style="font-size:22px;font-weight:700;color:#1a2332;border-bottom:2px solid #e5e7eb;padding-bottom:8px;margin-bottom:20px;">1. Business Unit Ranking</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;">
                <h3 style="font-size:16px;font-weight:600;margin-bottom:12px;">Total Projects</h3>
                <canvas id="buTotalChart" style="max-height:200px;"></canvas>
            </div>
            <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;">
                <h3 style="font-size:16px;font-weight:600;margin-bottom:12px;">Completed vs Overdue</h3>
                <canvas id="buCompleteOverdueChart" style="max-height:200px;"></canvas>
            </div>
            <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;grid-column:1/3;">
                <h3 style="font-size:16px;font-weight:600;margin-bottom:12px;">Status Distribution by Business Unit</h3>
                <canvas id="buStatusChart" style="max-height:250px;"></canvas>
            </div>
        </div>
    </section>

    <!-- Page 2: Assignee Ranking -->
    <section style="margin-bottom:48px;page-break-after:always;">
        <h2 style="font-size:22px;font-weight:700;color:#1a2332;border-bottom:2px solid #e5e7eb;padding-bottom:8px;margin-bottom:20px;">2. Assignee Ranking</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;">
                <h3 style="font-size:16px;font-weight:600;margin-bottom:12px;">Total Projects by Assignee</h3>
                <canvas id="assigneeTotalChart" style="max-height:200px;"></canvas>
            </div>
            <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;">
                <h3 style="font-size:16px;font-weight:600;margin-bottom:12px;">Completed vs Overdue</h3>
                <canvas id="assigneeCompleteOverdueChart" style="max-height:200px;"></canvas>
            </div>
            <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;grid-column:1/3;">
                <h3 style="font-size:16px;font-weight:600;margin-bottom:12px;">Assignee Performance (On Track / At Risk)</h3>
                <canvas id="assigneePerformanceChart" style="max-height:250px;"></canvas>
            </div>
        </div>
    </section>

    <!-- Page 3: Timeline Analysis -->
    <section style="margin-bottom:48px;">
        <h2 style="font-size:22px;font-weight:700;color:#1a2332;border-bottom:2px solid #e5e7eb;padding-bottom:8px;margin-bottom:20px;">3. Timeline Analysis</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:24px;">
            <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;text-align:center;">
                <div style="font-size:14px;color:#6b7280;">Shortest Timeline</div>
                <div style="font-size:32px;font-weight:800;color:#16a34a;"><?= $shortest ?> days</div>
                <div style="font-size:12px;color:#6b7280;margin-top:4px;">
                    <?php foreach ($shortestProjects as $sp) echo htmlspecialchars($sp['name']) . ' (' . $sp['business_unit'] . '), '; ?>
                </div>
            </div>
            <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;text-align:center;">
                <div style="font-size:14px;color:#6b7280;">Average Timeline</div>
                <div style="font-size:32px;font-weight:800;color:#3b82f6;"><?= $avg ?> days</div>
            </div>
            <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;text-align:center;">
                <div style="font-size:14px;color:#6b7280;">Longest Timeline</div>
                <div style="font-size:32px;font-weight:800;color:#dc2626;"><?= $longest ?> days</div>
                <div style="font-size:12px;color:#6b7280;margin-top:4px;">
                    <?php foreach ($longestProjects as $lp) echo htmlspecialchars($lp['name']) . ' (' . $lp['business_unit'] . '), '; ?>
                </div>
            </div>
        </div>
        <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:12px;">Project Duration Scatter</h3>
            <canvas id="timelineScatter" style="max-height:300px;"></canvas>
        </div>
    </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ---------- Business Unit Charts ----------
    const buNames = <?= json_encode(array_keys($buStats)) ?>;
    const buTotals = <?= json_encode(array_column($buStats, 'total')) ?>;
    const buCompleted = <?= json_encode(array_column($buStats, 'completed')) ?>;
    const buOverdue = <?= json_encode(array_column($buStats, 'overdue')) ?>;
    const buBehind = <?= json_encode(array_column($buStats, 'behind')) ?>;
    const buAtRisk = <?= json_encode(array_column($buStats, 'atrisk')) ?>;
    const buOnTrack = <?= json_encode(array_column($buStats, 'ontrack')) ?>;

    // Total Projects
    new Chart(document.getElementById('buTotalChart'), {
        type: 'bar',
        data: {
            labels: buNames,
            datasets: [{ label: 'Total Projects', data: buTotals, backgroundColor: '#1a4a7a' }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    // Completed vs Overdue
    new Chart(document.getElementById('buCompleteOverdueChart'), {
        type: 'bar',
        data: {
            labels: buNames,
            datasets: [
                { label: 'Completed', data: buCompleted, backgroundColor: '#3b82f6' },
                { label: 'Overdue', data: buOverdue, backgroundColor: '#dc2626' }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Status Distribution (stacked)
    new Chart(document.getElementById('buStatusChart'), {
        type: 'bar',
        data: {
            labels: buNames,
            datasets: [
                { label: 'On Track', data: buOnTrack, backgroundColor: '#16a34a' },
                { label: 'At Risk', data: buAtRisk, backgroundColor: '#f59e0b' },
                { label: 'Behind', data: buBehind, backgroundColor: '#9ca3af' },
                { label: 'Overdue', data: buOverdue, backgroundColor: '#dc2626' },
                { label: 'Complete', data: buCompleted, backgroundColor: '#3b82f6' }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { x: { stacked: true }, y: { stacked: true } } }
    });

    // ---------- Assignee Charts ----------
    const assigneeNames = <?= json_encode(array_keys($assigneeStats)) ?>;
    const assigneeTotals = <?= json_encode(array_column($assigneeStats, 'total')) ?>;
    const assigneeCompleted = <?= json_encode(array_column($assigneeStats, 'completed')) ?>;
    const assigneeOverdue = <?= json_encode(array_column($assigneeStats, 'overdue')) ?>;
    const assigneeAtRisk = <?= json_encode(array_column($assigneeStats, 'atrisk')) ?>;
    const assigneeOnTrack = <?= json_encode(array_column($assigneeStats, 'ontrack')) ?>;

    // Total
    new Chart(document.getElementById('assigneeTotalChart'), {
        type: 'bar',
        data: {
            labels: assigneeNames,
            datasets: [{ label: 'Total Projects', data: assigneeTotals, backgroundColor: '#1a4a7a' }]
        },
        options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
    });

    // Completed vs Overdue
    new Chart(document.getElementById('assigneeCompleteOverdueChart'), {
        type: 'bar',
        data: {
            labels: assigneeNames,
            datasets: [
                { label: 'Completed', data: assigneeCompleted, backgroundColor: '#3b82f6' },
                { label: 'Overdue', data: assigneeOverdue, backgroundColor: '#dc2626' }
            ]
        },
        options: { indexAxis: 'y', responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Performance (On Track vs At Risk)
    new Chart(document.getElementById('assigneePerformanceChart'), {
        type: 'bar',
        data: {
            labels: assigneeNames,
            datasets: [
                { label: 'On Track', data: assigneeOnTrack, backgroundColor: '#16a34a' },
                { label: 'At Risk', data: assigneeAtRisk, backgroundColor: '#f59e0b' }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    // ---------- Timeline Scatter ----------
    const durations = <?= json_encode(array_column($projects, 'duration')) ?>;
    const projectNames = <?= json_encode(array_column($projects, 'name')) ?>;
    const scatterData = durations.map((d, i) => ({ x: i+1, y: d, label: projectNames[i] }));
    new Chart(document.getElementById('timelineScatter'), {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Project Duration (days)',
                data: scatterData,
                backgroundColor: '#3b82f6',
                pointRadius: 5,
                pointHoverRadius: 8,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.raw.label + ': ' + ctx.raw.y + ' days';
                        }
                    }
                }
            },
            scales: {
                x: { title: { display: true, text: 'Project Index' } },
                y: { title: { display: true, text: 'Duration (days)' }, beginAtZero: true }
            }
        }
    });
});

<!-- Curated Project List -->
<section style="margin-top:48px;">
    <h3 style="font-size:18px;font-weight:700;color:#1a2332;border-bottom:1px solid #e5e7eb;padding-bottom:8px;margin-bottom:16px;">Curated Project Overview</h3>
    <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:2px solid #e5e7eb;">
                    <th style="padding:10px 14px;text-align:left;">Project</th>
                    <th style="padding:10px 14px;text-align:left;">Business Unit</th>
                    <th style="padding:10px 14px;text-align:left;">Status</th>
                    <th style="padding:10px 14px;text-align:left;">Assignee</th>
                    <th style="padding:10px 14px;text-align:right;">Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($projects, 0, 10) as $p): ?>
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:10px 14px;font-weight:500;"><?= htmlspecialchars($p['name']) ?></td>
                        <td style="padding:10px 14px;color:#4b5563;"><?= htmlspecialchars($p['business_unit']) ?></td>
                        <td style="padding:10px 14px;">
                            <span style="background:<?= getStatusColor($p['effective_status']) ?>;color:#fff;padding:2px 10px;border-radius:5px;font-size:12px;font-weight:600;">
                                <?= ucfirst($p['effective_status']) ?>
                            </span>
                        </td>
                        <td style="padding:10px 14px;color:#4b5563;"><?= htmlspecialchars($p['assignee_name'] ?? 'Unassigned') ?></td>
                        <td style="padding:10px 14px;text-align:right;font-weight:600;color:#1f2937;"><?= $p['progress'] ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

</script