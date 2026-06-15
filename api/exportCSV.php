<?php

session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["error"=>"unauthorized"]);
    exit;
}

require_once '../db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=projects.csv');

$output = fopen("php://output", "w");

fputcsv($output, [
    'Business Unit','Project','Stage','Status','Owner','Progress'
]);

$result = $conn->query("
SELECT b.name, p.name, p.stage, p.status, p.owner, p.progress
FROM projects p
JOIN business_units b ON p.business_unit_id=b.id
");

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
?>