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
            p.id, p.name, p.gate_due AS start, p.completion_due AS end,
            p.progress, p.status,
            b.name AS business_unit,
            u.full_name AS assignee_name,
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
        'ontrack' => '#16a34a',
        'atrisk' => '#f59e0b',
        'overdue' => '#dc2626',
        'behind' => '#9ca3af',
    ];
    while ($row = $result->fetch_assoc()) {
        if (!$row['start'] || !$row['end']) continue;
        $color = $colorMap[$row['effective_status']] ?? '#6b7280';
        $tasks[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'start' => $row['start'],
            'end' => $row['end'],
            'progress' => (int)$row['progress'],
            'color' => $color,
            'business_unit' => $row['business_unit'],
            'assignee' => $row['assignee_name'] ?? 'Unassigned',
        ];
    }

    echo json_encode($tasks);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}