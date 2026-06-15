<?php

session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["error"=>"unauthorized"]);
    exit;
}

require '../db.php';

require '../vendor/autoload.php'; // optional PHPExcel

use PhpOffice\PhpSpreadsheet\IOFactory;

$file = $_FILES['file']['tmp_name'];

$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray();

foreach ($rows as $i => $row) {

    if ($i == 0) continue; // skip header

    $business_unit = $row[0];
    $name = $row[1];

    $stmt = $conn->prepare("
    INSERT INTO projects (id, business_unit_id, name)
    VALUES (?, 
        (SELECT id FROM business_units WHERE name=?), 
        ?)
    ");

    $id = uniqid();

    $stmt->bind_param("sss", $id, $business_unit, $name);
    $stmt->execute();
}

echo json_encode(["status"=>"imported"]);


