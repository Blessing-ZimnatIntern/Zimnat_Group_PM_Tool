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
            p.id, p.name, p.stage, p.status, p.owner, p.assignee_id,
            p.gate_due, p.completion_due, p.progress,
            b.id AS business_unit_id, b.name AS business_unit_name,
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
    $projects = $result->fetch_all(MYSQLI_ASSOC);

    // Compute statistics
    $stats = [
        'total' => count($projects),
        'completed' => 0,
        'ontrack' => 0,
        'atrisk' => 0,
        'overdue' => 0,
        'behind' => 0,
    ];
    $progress_sum = 0;
    foreach ($projects as $p) {
        $statusKey = $p['effective_status'];
        $stats[$statusKey] = ($stats[$statusKey] ?? 0) + 1;
        $progress_sum += (int)$p['progress'];
    }
    $stats['avg_progress'] = $stats['total'] > 0 ? round($progress_sum / $stats['total']) : 0;

    // Get assignees
    $assignees = [];
    $assigneeSql = "SELECT DISTINCT u.id, u.full_name FROM users u JOIN projects p ON p.assignee_id = u.id WHERE p.deleted_at IS NULL";
    $assigneeRes = $conn->query($assigneeSql);
    while ($row = $assigneeRes->fetch_assoc()) {
        $assignees[] = $row;
    }

    // Business units
    $units = [];
    $unitSql = "SELECT id, name, color FROM business_units ORDER BY sort_order";
    $unitRes = $conn->query($unitSql);
    while ($row = $unitRes->fetch_assoc()) {
        $units[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'projects' => $projects,
        'statistics' => $stats,
        'assignees' => $assignees,
        'business_units' => $units,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}