<?php
require_once __DIR__ . '/../api/bootstrap.php';
use App\Config\Database;
use App\Middleware\Auth;

$currentUser = Auth::requireAuth();

$db = Database::getInstance();
$conn = $db->getConnection();

// Optional date range: scope the report to projects whose completion date falls in [from, to]
$dateFrom = $_GET['from'] ?? '';
$dateTo = $_GET['to'] ?? '';

// Fetch projects with business unit and assignee, scoped to the selected date range (if any)
$sql = "
    SELECT
        p.id, p.name, p.status, p.owner, p.owner_id, p.assignee_id, p.progress,
        p.gate_due, p.completion_due, p.created_at, p.completed_at,
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
$params = [];
$types = '';
if ($dateFrom) { $sql .= " AND p.completion_due >= ?"; $params[] = $dateFrom; $types .= 's'; }
if ($dateTo) { $sql .= " AND p.completion_due <= ?"; $params[] = $dateTo; $types .= 's'; }
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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

// 2. Assignee (Allocator) Performance — assignees are the editor/admin who allocated a
// project out (see Phase A), tracked via project_assignees. Editors only ever see their
// own row here; admins can see everyone, or drill into one specific allocator.
$isAdmin = $currentUser['role'] === 'admin';
$selectedAllocatorId = $isAdmin ? ($_GET['allocator_id'] ?? '') : (string)$currentUser['id'];

$allocatorSql = "
    SELECT pa.user_id, u.full_name AS allocator_name,
        CASE WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue' ELSE p.status END AS effective_status
    FROM project_assignees pa
    JOIN users u ON pa.user_id = u.id
    JOIN projects p ON pa.project_id = p.id
    WHERE p.deleted_at IS NULL
";
$allocatorParams = [];
$allocatorTypes = '';
if ($dateFrom) { $allocatorSql .= " AND p.completion_due >= ?"; $allocatorParams[] = $dateFrom; $allocatorTypes .= 's'; }
if ($dateTo) { $allocatorSql .= " AND p.completion_due <= ?"; $allocatorParams[] = $dateTo; $allocatorTypes .= 's'; }
if ($selectedAllocatorId !== '') { $allocatorSql .= " AND pa.user_id = ?"; $allocatorParams[] = (int)$selectedAllocatorId; $allocatorTypes .= 'i'; }

$allocStmt2 = $conn->prepare($allocatorSql);
if ($allocatorParams) $allocStmt2->bind_param($allocatorTypes, ...$allocatorParams);
$allocStmt2->execute();
$allocatorRows = $allocStmt2->get_result()->fetch_all(MYSQLI_ASSOC);

$assigneeStats = [];
foreach ($allocatorRows as $r) {
    $name = $r['allocator_name'];
    if (!isset($assigneeStats[$name])) {
        $assigneeStats[$name] = ['total'=>0, 'completed'=>0, 'overdue'=>0, 'atrisk'=>0, 'ontrack'=>0, 'behind'=>0];
    }
    $assigneeStats[$name]['total']++;
    $status = $r['effective_status'];
    if ($status === 'complete') $assigneeStats[$name]['completed']++;
    elseif ($status === 'overdue') $assigneeStats[$name]['overdue']++;
    elseif ($status === 'atrisk') $assigneeStats[$name]['atrisk']++;
    elseif ($status === 'behind') $assigneeStats[$name]['behind']++;
    else $assigneeStats[$name]['ontrack']++;
}

// Full editor/admin roster, used only to render the admin's drill-down selector
$allocatorOptions = $isAdmin
    ? $conn->query("SELECT id, full_name FROM users WHERE role IN ('admin','editor') AND is_active = 1 ORDER BY full_name")->fetch_all(MYSQLI_ASSOC)
    : [];

// 5. Owner (Developer) Productivity — owners are the developer doing the work (any role).
// Visible to everyone with report access; this is about team capacity planning, not gatekeeping.
$ownerStats = [];
foreach ($projects as $p) {
    if (empty($p['owner_id'])) continue;
    $name = $p['owner'] ?: 'Unknown';
    if (!isset($ownerStats[$name])) {
        $ownerStats[$name] = ['total' => 0, 'completed' => 0, 'active' => 0, 'totalDaysToClose' => 0, 'closedWithDuration' => 0];
    }
    $ownerStats[$name]['total']++;
    if ($p['effective_status'] === 'complete') {
        $ownerStats[$name]['completed']++;
        if (!empty($p['completed_at'])) {
            $days = (strtotime($p['completed_at']) - strtotime($p['created_at'])) / 86400;
            if ($days >= 0) {
                $ownerStats[$name]['totalDaysToClose'] += $days;
                $ownerStats[$name]['closedWithDuration']++;
            }
        }
    } else {
        $ownerStats[$name]['active']++;
    }
}

// "Sweet spot" = team's average active workload. Owners well above it risk overload;
// well below it have spare capacity — useful for deciding who gets the next assignment.
$activeWorkloads = array_column($ownerStats, 'active');
$ownersWithWork = array_filter($activeWorkloads, fn($a) => $a > 0);
$teamAvgWorkload = count($ownersWithWork) ? array_sum($ownersWithWork) / count($ownersWithWork) : 0;

foreach ($ownerStats as $name => &$s) {
    $s['completionRate'] = $s['total'] > 0 ? round(($s['completed'] / $s['total']) * 100) : 0;
    $s['avgDaysToClose'] = $s['closedWithDuration'] > 0 ? round($s['totalDaysToClose'] / $s['closedWithDuration']) : null;
    if ($teamAvgWorkload > 0 && $s['active'] > $teamAvgWorkload * 1.5) $s['workloadFlag'] = 'overloaded';
    elseif ($teamAvgWorkload > 0 && $s['active'] < $teamAvgWorkload * 0.5) $s['workloadFlag'] = 'underutilized';
    else $s['workloadFlag'] = 'balanced';
}
unset($s);

// 3. Timeline Analysis
$durations = array_filter(array_column($projects, 'duration'), function($d) { return $d !== null && $d > 0; });
sort($durations);
$shortest = $durations[0] ?? 0;
$longest = end($durations) ?? 0;
$avg = count($durations) ? round(array_sum($durations)/count($durations)) : 0;

$shortestProjects = array_filter($projects, function($p) use ($shortest) { return $p['duration'] == $shortest && $shortest > 0; });
$longestProjects = array_filter($projects, function($p) use ($longest) { return $p['duration'] == $longest && $longest > 0; });

// 4. Resource Allocation
$allocStmt = $conn->query("
    SELECT u.full_name, SUM(pa.allocation_pct) AS total_pct
    FROM project_assignees pa
    JOIN users u ON pa.user_id = u.id
    JOIN projects p ON pa.project_id = p.id
    WHERE p.deleted_at IS NULL
    GROUP BY u.full_name
    ORDER BY total_pct DESC
");
$allocationStats = $allocStmt->fetch_all(MYSQLI_ASSOC);

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
<div class="view-content" style="padding:24px;max-width:1400px;margin:0 auto;">

    <div style="display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:8px;">
        <div>
            <h1 style="font-size:28px;font-weight:800;color:#1a4a7a;margin-bottom:8px;">Project Management Report</h1>
            <p style="color:#6b7280;margin:0;">Generated: <?= date('Y-m-d H:i') ?><?= ($dateFrom || $dateTo) ? ' &middot; Completion date range: ' . htmlspecialchars($dateFrom ?: 'any') . ' to ' . htmlspecialchars($dateTo ?: 'any') : '' ?></p>
        </div>
        <form id="reportDateRangeForm" style="display:flex;align-items:center;gap:8px;">
            <label style="font-size:12px;color:#6b7280;">From <input type="date" name="from" value="<?= htmlspecialchars($dateFrom) ?>" style="margin-left:4px;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;"></label>
            <label style="font-size:12px;color:#6b7280;">To <input type="date" name="to" value="<?= htmlspecialchars($dateTo) ?>" style="margin-left:4px;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;"></label>
            <button type="submit" class="pill-btn">Apply</button>
            <button type="button" class="pill-btn" id="reportCopyBtn">Copy Summary</button>
            <a class="pill-btn" id="reportCsvBtn" href="#">CSV</a>
            <a class="pill-btn" id="reportExcelBtn" href="#">Excel</a>
            <a class="pill-btn primary" id="reportPdfBtn" href="#">Download PDF</a>
        </form>
    </div>

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

    <!-- Page 2: Owner (Developer) Productivity -->
    <section style="margin-bottom:48px;page-break-after:always;">
        <h2 style="font-size:22px;font-weight:700;color:#1a2332;border-bottom:2px solid #e5e7eb;padding-bottom:8px;margin-bottom:8px;">2. Owner (Developer) Productivity</h2>
        <p style="color:#6b7280;font-size:13px;margin:0 0 20px;">"Owner" is the developer doing the work — any role. Active workload = open (non-complete) projects currently assigned. Team average active workload: <b><?= round($teamAvgWorkload, 1) ?></b> — that's the "sweet spot"; owners well above it risk overload, well below it have spare capacity.</p>
        <?php if (empty($ownerStats)): ?>
            <p style="color:#9ca3af;text-align:center;padding:20px;">No projects have an assigned owner yet.</p>
        <?php else: ?>
        <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);overflow:hidden;margin-bottom:24px;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#f9fafb;border-bottom:2px solid #e5e7eb;">
                        <th style="padding:10px 14px;text-align:left;">Owner</th>
                        <th style="padding:10px 14px;text-align:right;">Total Projects</th>
                        <th style="padding:10px 14px;text-align:right;">Completed</th>
                        <th style="padding:10px 14px;text-align:right;">Completion Rate</th>
                        <th style="padding:10px 14px;text-align:right;">Avg Days to Close</th>
                        <th style="padding:10px 14px;text-align:right;">Active Workload</th>
                        <th style="padding:10px 14px;text-align:left;">Capacity</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($ownerStats as $name => $s): ?>
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:10px 14px;font-weight:500;"><?= htmlspecialchars($name) ?></td>
                        <td style="padding:10px 14px;text-align:right;"><?= $s['total'] ?></td>
                        <td style="padding:10px 14px;text-align:right;"><?= $s['completed'] ?></td>
                        <td style="padding:10px 14px;text-align:right;"><?= $s['completionRate'] ?>%</td>
                        <td style="padding:10px 14px;text-align:right;"><?= $s['avgDaysToClose'] !== null ? $s['avgDaysToClose'] . ' days' : '—' ?></td>
                        <td style="padding:10px 14px;text-align:right;"><?= $s['active'] ?></td>
                        <td style="padding:10px 14px;">
                            <?php
                                $flagColors = ['overloaded' => '#dc2626', 'underutilized' => '#f59e0b', 'balanced' => '#16a34a'];
                                $flagLabels = ['overloaded' => 'Overloaded', 'underutilized' => 'Spare capacity', 'balanced' => 'Balanced'];
                            ?>
                            <span style="background:<?= $flagColors[$s['workloadFlag']] ?>;color:#fff;padding:2px 10px;border-radius:5px;font-size:12px;font-weight:600;"><?= $flagLabels[$s['workloadFlag']] ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;">
                <h3 style="font-size:16px;font-weight:600;margin-bottom:12px;">Active Workload by Owner</h3>
                <canvas id="ownerWorkloadChart" style="max-height:220px;"></canvas>
            </div>
            <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;">
                <h3 style="font-size:16px;font-weight:600;margin-bottom:12px;">Completion Rate by Owner</h3>
                <canvas id="ownerCompletionChart" style="max-height:220px;"></canvas>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <!-- Page 3: Assignee (Allocator) Performance -->
    <section style="margin-bottom:48px;page-break-after:always;">
        <h2 style="font-size:22px;font-weight:700;color:#1a2332;border-bottom:2px solid #e5e7eb;padding-bottom:8px;margin-bottom:8px;">3. Assignee (Allocator) Performance</h2>
        <p style="color:#6b7280;font-size:13px;margin:0 0 16px;">"Assignee" is the editor/admin who allocated each project out — this shows how the projects they've handed out are tracking.</p>
        <?php if ($isAdmin): ?>
        <form id="allocatorSelectForm" style="margin-bottom:20px;">
            <label style="font-size:12px;color:#6b7280;">Viewing
                <select name="allocator_id" style="margin-left:6px;padding:6px 10px;border:1px solid #d1d5db;border-radius:6px;">
                    <option value="">All assignees</option>
                    <?php foreach ($allocatorOptions as $opt): ?>
                        <option value="<?= $opt['id'] ?>" <?= (string)$opt['id'] === $selectedAllocatorId ? 'selected' : '' ?>><?= htmlspecialchars($opt['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </form>
        <?php else: ?>
        <p style="color:#6b7280;font-size:13px;margin:0 0 16px;">Showing your own allocations only.</p>
        <?php endif; ?>
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
        <h2 style="font-size:22px;font-weight:700;color:#1a2332;border-bottom:2px solid #e5e7eb;padding-bottom:8px;margin-bottom:20px;">4. Timeline Analysis</h2>
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

    <!-- Page 4: Resource Allocation -->
    <section style="margin-bottom:48px;">
        <h2 style="font-size:22px;font-weight:700;color:#1a2332;border-bottom:2px solid #e5e7eb;padding-bottom:8px;margin-bottom:20px;">5. Resource Allocation</h2>
        <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);padding:20px;">
            <h3 style="font-size:16px;font-weight:600;margin-bottom:12px;">Allocation % by Team Member</h3>
            <?php if (empty($allocationStats)): ?>
                <p style="color:#9ca3af;text-align:center;padding:20px;">No assignees have been allocated to projects yet.</p>
            <?php else: ?>
                <canvas id="allocationChart" style="max-height:250px;"></canvas>
            <?php endif; ?>
        </div>
    </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    // This view is loaded via AJAX and its script is eval()'d after injection —
    // document's DOMContentLoaded has already fired by then, so run immediately instead.
    function reloadReportsView(extraParams) {
        const params = new URLSearchParams(extraParams);
        const from = dateRangeForm?.elements.from.value;
        const to = dateRangeForm?.elements.to.value;
        if (from && !params.has('from')) params.append('from', from);
        if (to && !params.has('to')) params.append('to', to);
        fetch(`views/reports.php?${params.toString()}`)
            .then(res => res.text())
            .then(html => {
                const content = document.getElementById('content');
                content.innerHTML = html;
                // innerHTML doesn't execute <script> tags — re-run them so charts render
                content.querySelectorAll('script').forEach(s => eval(s.textContent));
            });
    }
    const dateRangeForm = document.getElementById('reportDateRangeForm');
    if (dateRangeForm) {
        dateRangeForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const from = dateRangeForm.elements.from.value;
            const to = dateRangeForm.elements.to.value;
            const params = new URLSearchParams();
            if (from) params.append('from', from);
            if (to) params.append('to', to);
            reloadReportsView(params);
        });
    }
    const allocatorSelectForm = document.getElementById('allocatorSelectForm');
    if (allocatorSelectForm) {
        allocatorSelectForm.elements.allocator_id.addEventListener('change', function() {
            const params = new URLSearchParams();
            if (this.value) params.append('allocator_id', this.value);
            reloadReportsView(params);
        });
    }
    function reportExportParams() {
        const params = new URLSearchParams();
        const from = dateRangeForm?.elements.from.value;
        const to = dateRangeForm?.elements.to.value;
        if (from) params.append('from', from);
        if (to) params.append('to', to);
        return params;
    }
    ['reportCsvBtn', 'reportExcelBtn', 'reportPdfBtn'].forEach(function(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const type = id === 'reportCsvBtn' ? 'csv' : (id === 'reportExcelBtn' ? 'excel' : 'pdf');
            window.location.href = `api/export/${type}.php?${reportExportParams().toString()}`;
        });
    });
    const copyBtn = document.getElementById('reportCopyBtn');
    if (copyBtn) {
        copyBtn.addEventListener('click', function() {
            const lines = [
                'Project Management Report',
                'Generated: ' + new Date().toISOString().slice(0, 16).replace('T', ' '),
                '',
                'Business Unit Totals:',
                <?= json_encode(implode("\\n", array_map(fn($bu) => "  $bu: {$buStats[$bu]['total']} total, {$buStats[$bu]['completed']} completed, {$buStats[$bu]['overdue']} overdue", array_keys($buStats)))) ?>,
            ].join('\n');
            navigator.clipboard.writeText(lines).then(() => {
                alert('Report summary copied to clipboard.');
            }).catch(() => alert('Could not copy to clipboard.'));
        });
    }
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

    // ---------- Owner (Developer) Productivity ----------
    const ownerWorkloadCanvas = document.getElementById('ownerWorkloadChart');
    if (ownerWorkloadCanvas) {
        const ownerNames = <?= json_encode(array_keys($ownerStats)) ?>;
        const ownerActive = <?= json_encode(array_column($ownerStats, 'active')) ?>;
        const teamAvgWorkload = <?= json_encode(round($teamAvgWorkload, 1)) ?>;
        new Chart(ownerWorkloadCanvas, {
            type: 'bar',
            data: {
                labels: ownerNames,
                datasets: [{
                    label: 'Active Projects',
                    data: ownerActive,
                    backgroundColor: ownerActive.map(a => a > teamAvgWorkload * 1.5 ? '#dc2626' : (a < teamAvgWorkload * 0.5 ? '#f59e0b' : '#16a34a')),
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, title: { display: true, text: 'Active (non-complete) projects' } } }
            }
        });
    }
    const ownerCompletionCanvas = document.getElementById('ownerCompletionChart');
    if (ownerCompletionCanvas) {
        const ownerNames2 = <?= json_encode(array_keys($ownerStats)) ?>;
        const ownerRates = <?= json_encode(array_column($ownerStats, 'completionRate')) ?>;
        new Chart(ownerCompletionCanvas, {
            type: 'bar',
            data: {
                labels: ownerNames2,
                datasets: [{ label: 'Completion Rate %', data: ownerRates, backgroundColor: '#1a4a7a', borderRadius: 4 }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 100, title: { display: true, text: '% completed' } } }
            }
        });
    }

    // ---------- Resource Allocation ----------
    const allocCanvas = document.getElementById('allocationChart');
    if (allocCanvas) {
        const allocNames = <?= json_encode(array_column($allocationStats, 'full_name')) ?>;
        const allocPcts = <?= json_encode(array_map('intval', array_column($allocationStats, 'total_pct'))) ?>;
        new Chart(allocCanvas, {
            type: 'bar',
            data: {
                labels: allocNames,
                datasets: [{
                    label: 'Allocation %',
                    data: allocPcts,
                    backgroundColor: allocPcts.map(p => p > 100 ? '#dc2626' : '#2a5a8c'),
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, title: { display: true, text: '% of capacity' } } }
            }
        });
    }
})();
</script>

<!-- Curated Project List -->
<section style="margin-top:48px;max-width:1400px;margin-left:auto;margin-right:auto;">
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