<?php
session_start();
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
$assignee = $_GET['assignee'] ?? null;
$search = $_GET['search'] ?? null;

$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "
    SELECT 
        p.name AS project_name,
        b.name AS business_unit,
        p.stage,
        p.status,
        p.owner,
        p.assignee,
        p.priority,
        p.gate_due,
        p.completion_due,
        p.progress,
        p.current_update,
        p.next_steps
    FROM projects p
    JOIN business_units b ON p.business_unit_id = b.id
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
if ($assignee) {
    $sql .= " AND p.assignee = ?";
    $params[] = $assignee;
    $types .= 's';
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
    'Priority', 'Gate Due', 'Completion Due', 'Progress %', 'Current Update', 'Next Steps'
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
foreach (range('A', 'L') as $c) {
    $sheet->getColumnDimension($c)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="projects_export_' . date('Y-m-d') . '.xlsx"');
$writer->save('php://output');
exit;