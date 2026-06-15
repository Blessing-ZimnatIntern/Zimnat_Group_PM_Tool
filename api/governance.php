<?php

session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["error"=>"unauthorized"]);
    exit;
}

require_once '../db.php';
require_once '../utils.php';

$project_id = $_GET['project_id'] ?? null;

if (!$project_id) {
    respond(["status"=>"error","message"=>"Missing project ID"]);
}

$stmt = $conn->prepare("
    SELECT item, stage, status
    FROM governance
    WHERE project_id = ?
");

$stmt->bind_param("s", $project_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

respond(["status"=>"success","data"=>$data]);