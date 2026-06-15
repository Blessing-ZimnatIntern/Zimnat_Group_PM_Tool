<?php
require '../db.php';

$file = $_FILES['file']['tmp_name'];
$handle = fopen($file, "r");

$rowIndex = 0;

while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {

    if ($rowIndex == 0) {
        $rowIndex++;
        continue;
    }

    $bu = $row[0];
    $name = $row[1];
    $stage = $row[2];
    $status = $row[3];
    $owner = $row[4];
    $priority = $row[5];
    $gate = $row[6];
    $comp = $row[7];
    $progress = $row[8];

    $id = uniqid();

    $stmt = $conn->prepare("
    INSERT INTO projects
    VALUES (?, 
      (SELECT id FROM business_units WHERE name=?),
      ?, ?, ?, ?, ?, ?, ?, ?,  '', ''
    )
    ");

    $stmt->bind_param(
      "sssssssssi",
      $id,$bu,$name,$stage,$status,$owner,$priority,$gate,$comp,$progress
    );

    $stmt->execute();

    $rowIndex++;
}

fclose($handle);

echo json_encode(["status"=>"imported"]);
?>