<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

$user = Auth::requireAuth();

$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "SELECT p.id, p.name AS text, p.stage, p.gate_due AS start, p.completion_due AS end, p.progress, p.status, b.name AS business_unit FROM projects p JOIN business_units b ON p.business_unit_id = b.id WHERE p.deleted_at IS NULL ORDER BY p.gate_due ASC";
$result = $conn->query($sql);
$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = [
        'id' => $row['id'],
        'text' => $row['text'] . ' (' . $row['business_unit'] . ')',
        'start' => $row['start'],
        'end' => $row['end'],
        'progress' => $row['progress'] / 100,
        'custom_class' => 'gantt-' . $row['status'],
        'business_unit' => $row['business_unit']
    ];
}
header('Content-Type: application/json');
echo json_encode($tasks);