<?php
// api/helpers/ExecutiveSummary.php
// Template-based narrative generation over portfolio stats — no LLM call.
// Same technique BI tools (Power BI "Insights", Tableau "Explain Data") use for auto-generated commentary.

function buildExecutiveSummary($conn, $businessUnitId = null) {
    $where = "WHERE p.deleted_at IS NULL";
    $params = [];
    $types = '';
    if ($businessUnitId) {
        $where .= " AND p.business_unit_id = ?";
        $params[] = $businessUnitId;
        $types .= 'i';
    }

    $sql = "
        SELECT p.id, p.name, p.progress, p.budget, p.actual_cost, p.gate_due, p.completion_due,
               b.name AS business_unit,
               CASE WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue' ELSE p.status END AS effective_status
        FROM projects p
        JOIN business_units b ON p.business_unit_id = b.id
        $where
    ";
    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $projects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $total = count($projects);
    if ($total === 0) {
        return [
            'summary' => 'There are no active projects in scope to summarize.',
            'generated_at' => date('Y-m-d H:i'),
        ];
    }

    $counts = ['ontrack' => 0, 'atrisk' => 0, 'behind' => 0, 'overdue' => 0, 'complete' => 0];
    $totalBudget = 0;
    $totalActual = 0;
    $totalProgress = 0;
    $riskProjects = [];
    foreach ($projects as $p) {
        $es = $p['effective_status'];
        if (isset($counts[$es])) $counts[$es]++;
        $totalBudget += (float)($p['budget'] ?? 0);
        $totalActual += (float)($p['actual_cost'] ?? 0);
        $totalProgress += (int)$p['progress'];
        if (in_array($es, ['overdue', 'behind', 'atrisk'], true)) {
            $riskProjects[] = $p;
        }
    }
    $avgProgress = round($totalProgress / $total);
    $healthyPct = round((($counts['ontrack'] + $counts['complete']) / $total) * 100);

    // Rank risk projects: overdue first, then behind, then at-risk
    $statusRank = ['overdue' => 0, 'behind' => 1, 'atrisk' => 2];
    usort($riskProjects, fn($a, $b) => $statusRank[$a['effective_status']] <=> $statusRank[$b['effective_status']]);
    $topRisk = array_slice($riskProjects, 0, 3);

    // Overallocated resources (only meaningful at portfolio scope, since allocation isn't BU-scoped)
    $overallocated = [];
    if (!$businessUnitId) {
        $allocStmt = $conn->query("
            SELECT u.full_name, SUM(pa.allocation_pct) AS total_pct
            FROM project_assignees pa
            JOIN users u ON pa.user_id = u.id
            JOIN projects p ON pa.project_id = p.id
            WHERE p.deleted_at IS NULL
            GROUP BY u.full_name
            HAVING total_pct > 100
            ORDER BY total_pct DESC
        ");
        $overallocated = $allocStmt->fetch_all(MYSQLI_ASSOC);
    }

    $sentences = [];

    // 1. Opening — overall health framing
    if ($healthyPct >= 80) {
        $sentences[] = "The portfolio of {$total} active projects is in strong shape, with {$healthyPct}% on track or complete and an average progress of {$avgProgress}%.";
    } elseif ($healthyPct >= 60) {
        $sentences[] = "The portfolio of {$total} active projects is broadly stable, with {$healthyPct}% on track or complete, though a meaningful share need attention.";
    } elseif ($healthyPct >= 40) {
        $sentences[] = "The portfolio of {$total} active projects shows mixed health — only {$healthyPct}% are on track or complete, and a significant proportion are behind schedule or at risk.";
    } else {
        $sentences[] = "The portfolio of {$total} active projects is under strain: just {$healthyPct}% are on track or complete, and immediate intervention is recommended across multiple projects.";
    }

    // 2. Risk callout — name specific projects
    if (!empty($topRisk)) {
        $names = array_map(fn($p) => '"' . $p['name'] . '" (' . $p['business_unit'] . ')', $topRisk);
        $namesList = count($names) === 1 ? $names[0] : (implode(', ', array_slice($names, 0, -1)) . ' and ' . end($names));
        $riskCount = $counts['overdue'] + $counts['behind'] + $counts['atrisk'];
        $sentences[] = "{$riskCount} project" . ($riskCount === 1 ? ' is' : 's are') . " currently overdue, behind schedule, or at risk, most notably {$namesList}.";
    } else {
        $sentences[] = "No projects are currently flagged as overdue, behind schedule, or at risk.";
    }

    // 3. Budget commentary
    if ($totalBudget > 0) {
        $variance = $totalActual - $totalBudget;
        $variancePct = round((abs($variance) / $totalBudget) * 100);
        if ($variance > 0 && $variancePct >= 5) {
            $sentences[] = "Spending is running over budget by \$" . number_format($variance, 0) . " ({$variancePct}% above plan) across the portfolio.";
        } elseif ($variance < 0 && $variancePct >= 5) {
            $sentences[] = "The portfolio is currently \$" . number_format(abs($variance), 0) . " ({$variancePct}%) under budget.";
        } else {
            $sentences[] = "Overall spending is tracking close to budget.";
        }
    }

    // 4. Resourcing commentary (portfolio-level only)
    if (!$businessUnitId) {
        if (!empty($overallocated)) {
            $names = array_map(fn($u) => $u['full_name'] . ' (' . (int)$u['total_pct'] . '%)', array_slice($overallocated, 0, 3));
            $sentences[] = count($overallocated) . " team member" . (count($overallocated) === 1 ? ' is' : 's are') . " currently allocated beyond full capacity — " . implode(', ', $names) . " — which may put delivery timelines at risk.";
        } else {
            $sentences[] = "No team members are currently over-allocated across active projects.";
        }
    }

    // 5. Closing recommendation
    if (!empty($topRisk)) {
        $sentences[] = "Recommended focus: review " . $topRisk[0]['name'] . ($topRisk[0]['effective_status'] === 'overdue' ? ', which is overdue,' : '') . " and confirm a recovery plan with the responsible business unit.";
    } else {
        $sentences[] = "No immediate escalations are required at this time.";
    }

    return [
        'summary' => implode(' ', $sentences),
        'stats' => [
            'total' => $total,
            'healthy_pct' => $healthyPct,
            'avg_progress' => $avgProgress,
            'counts' => $counts,
            'total_budget' => $totalBudget,
            'total_actual' => $totalActual,
            'overallocated_count' => count($overallocated),
        ],
        'generated_at' => date('Y-m-d H:i'),
    ];
}
