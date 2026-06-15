<?php
require$data = json_decode(file_get_contents("php://input"), true);require '../db.php';

$stmt = $conn->prepare("
REPLACE INTO governance (project_id, item, stage, status)
VALUES (?, ?, ?, ?)
");

$stmt->bind_param(
    "ssss",
    $data['project_id'],
    $data['item'],
    $data['stage'],
    $data['status']
);

$stmt->execute();

echo json_encode(["status" => "success"]);

