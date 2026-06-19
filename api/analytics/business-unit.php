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

$unitId = $_GET['id'] ?? null;
$unitName = $_GET['name'] ?? null;

if (!$unitId && !$unitName) {
    http_response_code(400);
    echo json_encode(['error' => 'Business unit ID or name is required']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Get business unit details
    if ($unitId) {
        $buStmt = $conn->prepare("SELECT id, name, color FROM business_units WHERE id = ?");
        $buStmt->bind_param("i", $unitId);
    } else {
        $buStmt = $conn->prepare("SELECT id, name, color FROM business_units WHERE name = ?");
        $buStmt->bind_param("s", $unitName);
    }
    $buStmt->execute();
    $businessUnit = $buStmt->get_result()->fetch_assoc();

    if (!$businessUnit) {
        http_response_code(404);
        echo json_encode(['error' => 'Business unit not found']);
        exit;
    }

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

    $where = "p.business_unit_id = ?";
    if ($hasDeletedAt) {
        $where .= " AND p.deleted_at IS NULL";
    }

    $sql = "
        SELECT 
            p.*,
            CASE 
                WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue'
                ELSE p.status
            END AS effective_status,
            CASE 
                WHEN p.gate_due < CURDATE() AND p.status != 'complete' THEN 1
                ELSE 0
            END AS is_gate_overdue
        FROM projects p
        WHERE $where
        ORDER BY p.completion_due ASC, p.name
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $businessUnit['id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $projects = [];
    $stats = [
        'total' => 0,
        'ontrack' => 0,
        'atrisk' => 0,
        'behind' => 0,
        'complete' => 0,
        'overdue' => 0,
        'gate_overdue' => 0,
        'avg_progress' => 0,
        'stage_distribution' => [
            'initiation' => 0,
            'planning' => 0,
            'execution' => 0,
            'qa' => 0,
            'uat' => 0,
            'closure' => 0
        ]
    ];

    $totalProgress = 0;

    while ($row = $result->fetch_assoc()) {
        $status = $row['effective_status'];
        $stats[$status]++;
        $stats['stage_distribution'][$row['stage']]++;
        if ((int)$row['is_gate_overdue'] === 1) $stats['gate_overdue']++;
        $totalProgress += (int)$row['progress'];
        $projects[] = $row;
    }

    $stats['total'] = count($projects);
    $stats['avg_progress'] = $stats['total'] > 0 ? round($totalProgress / $stats['total']) : 0;

    // Get governance completion for each project
    $projectIds = array_column($projects, 'id');
    if (!empty($projectIds)) {
        $placeholders = implode(',', array_fill(0, count($projectIds), '?'));
        $types = str_repeat('s', count($projectIds));
        $govStmt = $conn->prepare("
            SELECT 
                project_id,
                COUNT(*) AS total_items,
                SUM(CASE WHEN status = 'signed' THEN 1 ELSE 0 END) AS signed_count,
                SUM(CASE WHEN status != 'na' THEN 1 ELSE 0 END) AS applicable_count
            FROM governance_items
            WHERE project_id IN ($placeholders)
            GROUP BY project_id
        ");
        $govStmt->bind_param($types, ...$projectIds);
        $govStmt->execute();
        $govResult = $govStmt->get_result();
        $govMap = [];
        while ($row = $govResult->fetch_assoc()) {
            $applicable = (int)$row['applicable_count'];
            $govMap[$row['project_id']] = $applicable > 0 
                ? round(((int)$row['signed_count'] / $applicable) * 100) 
                : 100;
        }
        foreach ($projects as &$p) {
            $p['governance_completion'] = $govMap[$p['id']] ?? 0;
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'business_unit' => $businessUnit,
            'statistics' => $stats,
            'projects' => $projects
        ]
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage()
    ]);
}