<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();

try {
    $conn = Database::getInstance()->getConnection();

    $cellsStmt = $conn->query("
        SELECT pa.project_id, pa.user_id, pa.allocation_pct,
               u.full_name AS user_name,
               p.name AS project_name,
               b.name AS business_unit
        FROM project_assignees pa
        JOIN users u ON pa.user_id = u.id
        JOIN projects p ON pa.project_id = p.id
        JOIN business_units b ON p.business_unit_id = b.id
        WHERE p.deleted_at IS NULL
        ORDER BY u.full_name, b.sort_order, p.name
    ");
    $cells = $cellsStmt->fetch_all(MYSQLI_ASSOC);

    $users = [];
    $projects = [];
    foreach ($cells as $c) {
        $users[$c['user_id']] = $c['user_name'];
        $projects[$c['project_id']] = ['name' => $c['project_name'], 'business_unit' => $c['business_unit']];
    }

    $totalsStmt = $conn->query("
        SELECT pa.user_id, u.full_name, SUM(pa.allocation_pct) AS total_pct
        FROM project_assignees pa
        JOIN users u ON pa.user_id = u.id
        JOIN projects p ON pa.project_id = p.id
        WHERE p.deleted_at IS NULL
        GROUP BY pa.user_id, u.full_name
        ORDER BY total_pct DESC
    ");
    $totals = $totalsStmt->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'status' => 'success',
        'users' => array_map(fn($id, $name) => ['id' => $id, 'full_name' => $name], array_keys($users), array_values($users)),
        'projects' => array_map(fn($id, $p) => ['id' => $id, 'name' => $p['name'], 'business_unit' => $p['business_unit']], array_keys($projects), array_values($projects)),
        'cells' => $cells,
        'totals' => $totals,
        'overallocated' => array_values(array_filter($totals, fn($t) => (int)$t['total_pct'] > 100))
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
