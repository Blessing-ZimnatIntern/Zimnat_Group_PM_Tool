<?php

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load bootstrap
require_once __DIR__ . '/../../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

// Enforce authentication
$user = Auth::requireAuth();

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Check if soft delete column exists
    $colStmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'projects'
          AND COLUMN_NAME = 'deleted_at'
    ");
    $colStmt->execute();
    $hasDeletedAt = ((int)$colStmt->get_result()->fetch_assoc()['total']) > 0;

    $where = $hasDeletedAt ? "WHERE p.deleted_at IS NULL" : "";

    $sql = "
        SELECT 
            p.*,
            b.id AS business_unit_id,
            b.name AS business_unit_name,
            b.color AS business_unit_color,
            CASE 
                WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue'
                ELSE p.status
            END AS effective_status
        FROM projects p
        JOIN business_units b ON p.business_unit_id = b.id
        $where
        ORDER BY b.sort_order, p.completion_due ASC
    ";

    $result = $conn->query($sql);
    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }

    // Calculate statistics
    $stats = [
        'total' => count($projects),
        'by_status' => [
            'ontrack' => 0,
            'atrisk' => 0,
            'behind' => 0,
            'complete' => 0,
            'overdue' => 0
        ],
        'avg_progress' => 0,
        'by_business_unit' => []
    ];

    $total_progress = 0;

    foreach ($projects as $project) {
        $status = $project['effective_status'];
        $stats['by_status'][$status]++;
        $total_progress += (int)$project['progress'];

        // Group by business unit
        $bu = $project['business_unit_name'];
        if (!isset($stats['by_business_unit'][$bu])) {
            $stats['by_business_unit'][$bu] = [
                'id' => $project['business_unit_id'],
                'name' => $bu,
                'color' => $project['business_unit_color'],
                'total' => 0,
                'complete' => 0,
                'overdue' => 0,
                'total_progress' => 0
            ];
        }
        $stats['by_business_unit'][$bu]['total']++;
        if ($status === 'complete') $stats['by_business_unit'][$bu]['complete']++;
        if ($status === 'overdue') $stats['by_business_unit'][$bu]['overdue']++;
        $stats['by_business_unit'][$bu]['total_progress'] += (int)$project['progress'];
    }

    // Calculate averages
    $stats['avg_progress'] = $stats['total'] > 0 ? round($total_progress / $stats['total']) : 0;
    foreach ($stats['by_business_unit'] as &$bu) {
        $bu['avg_progress'] = $bu['total'] > 0 ? round($bu['total_progress'] / $bu['total']) : 0;
        $bu['completion_rate'] = $bu['total'] > 0 ? round(($bu['complete'] / $bu['total']) * 100) : 0;
    }

    // Get needs attention projects
    $attention = array_filter($projects, function($p) {
        return in_array($p['effective_status'], ['overdue', 'behind', 'atrisk']);
    });
    usort($attention, function($a, $b) {
        return strcmp($a['completion_due'] ?? '9999-12-31', $b['completion_due'] ?? '9999-12-31');
    });

    echo json_encode([
        'status' => 'success',
        'data' => [
            'statistics' => $stats,
            'needs_attention' => array_slice($attention, 0, 20),
            'all_projects' => $projects
        ]
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage()
    ]);
}