<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$user = Auth::requireAuth();

// Get filters (same as CSV)
$unit = $_GET['unit'] ?? null;
$status = $_GET['status'] ?? null;
$owner = $_GET['owner'] ?? null;
$ownerId = $_GET['owner_id'] ?? null;
$assignee = $_GET['assignee'] ?? null;
$search = $_GET['search'] ?? null;
$dateFrom = $_GET['from'] ?? null;
$dateTo = $_GET['to'] ?? null;

$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "
    SELECT
        p.name AS project_name,
        b.name AS business_unit,
        p.stage,
        p.status,
        p.owner,
        u.full_name AS assignee,
        p.priority,
        p.gate_due,
        p.completion_due,
        p.progress,
        p.budget,
        p.actual_cost,
        p.currency,
        (SELECT GROUP_CONCAT(u2.full_name, ' (', pa.allocation_pct, '%)' SEPARATOR '; ')
            FROM project_assignees pa JOIN users u2 ON pa.user_id = u2.id
            WHERE pa.project_id = p.id) AS team_assignees,
        p.current_update,
        p.next_steps
    FROM projects p
    JOIN business_units b ON p.business_unit_id = b.id
    LEFT JOIN users u ON p.assignee_id = u.id
    WHERE p.deleted_at IS NULL
";

$params = [];
$types = '';

if ($unit) {
    $sql .= " AND b.name = ?";
    $params[] = $unit;
    $types .= 's';
}
if ($status) {
    $sql .= " AND p.status = ?";
    $params[] = $status;
    $types .= 's';
}
if ($owner) {
    $sql .= " AND p.owner = ?";
    $params[] = $owner;
    $types .= 's';
}
if ($ownerId) {
    $sql .= " AND p.owner_id = ?";
    $params[] = (int)$ownerId;
    $types .= 'i';
}
if ($assignee) {
    $sql .= " AND p.assignee_id = ?";
    $params[] = (int)$assignee;
    $types .= 'i';
}
if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.owner LIKE ? OR p.current_update LIKE ? OR p.next_steps LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'ssss';
}
if ($dateFrom) {
    $sql .= " AND p.completion_due >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}
if ($dateTo) {
    $sql .= " AND p.completion_due <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$headers = [
    'Project Name', 'Business Unit', 'Stage', 'Status', 'Owner', 'Assignee',
    'Priority', 'Gate Due', 'Completion Due', 'Progress %', 'Budget', 'Actual Cost', 'Currency',
    'Team Assignees (Allocation %)', 'Current Update', 'Next Steps'
];
$col = 'A';
foreach ($headers as $h) {
    $sheet->setCellValue($col . '1', $h);
    $col++;
}

$row = 2;
while ($data = $result->fetch_assoc()) {
    $col = 'A';
    foreach ($data as $value) {
        $sheet->setCellValue($col . $row, $value);
        $col++;
    }
    $row++;
}

// Auto-size columns
foreach (range('A', 'P') as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="projects_export_' . date('Y-m-d') . '.xlsx"');
$writer->save('php://output');
exit;