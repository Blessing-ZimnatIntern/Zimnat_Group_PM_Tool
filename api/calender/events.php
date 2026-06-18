<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

try {
    $user = Auth::requireAuth();
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $sql = "SELECT p.id, p.name AS title, p.gate_due AS start, p.completion_due AS end, p.status, b.name AS business_unit, CASE WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue' ELSE p.status END AS effective_status FROM projects p JOIN business_units b ON p.business_unit_id = b.id WHERE p.deleted_at IS NULL";
    $result = $conn->query($sql);
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $color = '#1976D2';
        if ($row['effective_status'] === 'overdue') $color = '#D32F2F';
        elseif ($row['effective_status'] === 'behind') $color = '#F9A825';
        elseif ($row['effective_status'] === 'atrisk') $color = '#FB8C00';
        elseif ($row['effective_status'] === 'ontrack') $color = '#2E7D32';
        elseif ($row['effective_status'] === 'complete') $color = '#1976D2';
        $events[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'start' => $row['start'],
            'end' => $row['end'],
            'color' => $color,
            'textColor' => '#fff',
            'extendedProps' => [
                'business_unit' => $row['business_unit'],
                'status' => $row['status'],
                'effective_status' => $row['effective_status']
            ]
        ];
    }
    echo json_encode($events);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}