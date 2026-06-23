<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();
$businessUnit = isset($_GET['business_unit']) ? (int)$_GET['business_unit'] : null;
$limit = min(100, max(1, (int)($_GET['limit'] ?? 25)));

function fieldLabel($f) {
    $map = [
        'stage' => 'stage gate', 'status' => 'status', 'owner' => 'owner', 'priority' => 'priority',
        'gate_due' => 'stage gate due date', 'completion_due' => 'completion date', 'progress' => 'progress',
        'current_update' => 'current update', 'next_steps' => 'next steps', 'budget' => 'budget',
        'actual_cost' => 'actual cost', 'currency' => 'currency', 'assignee_id' => 'assignee', 'name' => 'name',
        'business_unit_id' => 'business unit', 'progress (auto)' => 'progress',
    ];
    return $map[$f] ?? str_replace('_', ' ', $f);
}

function governanceItemLabel($key) {
    $itemLabels = [
        'boscard' => 'BOSCARD', 'brd' => 'BRD', 'plan' => 'Project Plan',
        'qa_report' => 'QA Report', 'uat_signoff' => 'UAT Sign-off',
        'golive_cr' => 'Go-live CR', 'closure_report' => 'Closure Report'
    ];
    return $itemLabels[$key] ?? ($key ?: 'a governance item');
}

function describeActivity($log) {
    $action = $log['action'];
    $details = json_decode($log['details'] ?? '{}', true) ?: [];
    $name = $log['project_name'] ?? $details['name'] ?? ($details['project']['name'] ?? null) ?? ($details['new']['name'] ?? null) ?? 'a project';

    switch ($action) {
        case 'create':
            return "created project \"$name\"";
        case 'delete':
            return "deleted project \"$name\"";
        case 'attachment_upload':
            $item = governanceItemLabel($details['item_key'] ?? '');
            return "attached \"" . ($details['file_name'] ?? 'a file') . "\" to $item on \"$name\"";
        case 'attachment_delete':
            $item = governanceItemLabel($details['item_key'] ?? '');
            return "removed \"" . ($details['file_name'] ?? 'a file') . "\" from $item on \"$name\"";
        case 'comment':
            return "commented on \"$name\"";
        case 'stakeholder_add':
            return "added \"" . ($details['name'] ?? 'a stakeholder') . "\" as a stakeholder on \"$name\"";
        case 'stakeholder_document_upload':
            return "uploaded \"" . ($details['file_name'] ?? 'a file') . "\" for a stakeholder on \"$name\"";
        case 'governance_update':
            $item = governanceItemLabel($details['item_key'] ?? '');
            $statusLabels = ['pending' => 'Pending', 'progress' => 'In progress', 'signed' => 'Signed off', 'na' => 'N/A'];
            $status = $statusLabels[$details['status'] ?? ''] ?? ucfirst($details['status'] ?? '');
            return "marked \"$item\" as $status on \"$name\"";
        case 'update':
            $fields = array_unique(array_map('fieldLabel', $details['changed_fields'] ?? []));
            if (empty($fields)) return "updated \"$name\"";
            return "updated " . implode(', ', $fields) . " on \"$name\"";
        default:
            return "$action on \"$name\"";
    }
}

try {
    $conn = Database::getInstance()->getConnection();

    $sql = "
        SELECT a.id, a.action, a.entity_type, a.entity_id, a.details, a.created_at,
               u.full_name AS user_name,
               p.name AS project_name, p.business_unit_id
        FROM activity_logs a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN projects p ON a.entity_type = 'project' AND a.entity_id = p.id
        WHERE 1=1
    ";
    $params = [];
    $types = '';
    if ($businessUnit) {
        $sql .= " AND p.business_unit_id = ?";
        $params[] = $businessUnit;
        $types .= 'i';
    }
    $sql .= " ORDER BY a.created_at DESC LIMIT ?";
    $params[] = $limit;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $activity = array_map(function ($log) {
        return [
            'id' => $log['id'],
            'user_name' => $log['user_name'] ?? 'Someone',
            'description' => describeActivity($log),
            'project_id' => $log['entity_type'] === 'project' ? $log['entity_id'] : null,
            'created_at' => $log['created_at'],
        ];
    }, $logs);

    echo json_encode(['status' => 'success', 'data' => $activity]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
