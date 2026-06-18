<?php
require_once __DIR__ . '/bootstrap.php';
use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

try {
    $user = Auth::requireAuth();
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $assignee = $_GET['assignee'] ?? null;
    $status = $_GET['status'] ?? null;
    $business_unit = $_GET['business_unit'] ?? null;

    $sql = "
        SELECT 
            p.id, p.name, 
            COALESCE(p.gate_due, p.created_at) AS start, 
            COALESCE(p.completion_due, DATE_ADD(p.created_at, INTERVAL 30 DAY)) AS end,
            p.progress,
            CASE 
                WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue'
                ELSE p.status
            END AS effective_status
        FROM projects p
        JOIN business_units b ON p.business_unit_id = b.id
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
    $result = $stmt->get_result();
    $tasks = [];
    $colorMap = [
        'complete' => '#3b82f6',
        'ontrack'  => '#16a34a',
        'atrisk'   => '#f59e0b',
        'overdue'  => '#dc2626',
        'behind'   => '#9ca3af',
    ];
    while ($row = $result->fetch_assoc()) {
        // Skip if no valid dates
        if (!$row['start'] || !$row['end']) continue;
        $color = $colorMap[$row['effective_status']] ?? '#6b7280';
        $tasks[] = [
            'id'          => $row['id'],
            'name'        => $row['name'],
            'start'       => $row['start'],
            'end'         => $row['end'],
            'progress'    => (int)$row['progress'] / 100,
            'custom_class'=> 'gantt-' . $row['effective_status'],
        ];
    }

    echo json_encode($tasks);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}