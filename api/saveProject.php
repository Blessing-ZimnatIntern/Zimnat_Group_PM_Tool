<?php

session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["error"=>"unauthorized"]);
    exit;
}

require_once '../db.php';
require_once '../utils.php';

$data = jsonInput();

$id = $data['id'];
$business_unit = $data['business_unit']; // name
$name = $data['name'];
$stage = $data['stage'];
$status = $data['status'];
$owner = $data['owner'];
$priority = $data['priority'];
$gate_due = $data['gate_due'];
$completion_due = $data['completion_due'];
$progress = $data['progress'];
$current_update = $data['current_update'];
$next_steps = $data['next_steps'];

// --- get business_unit_id ---
$stmt = $conn->prepare("SELECT id FROM business_units WHERE name = ?");
$stmt->bind_param("s", $business_unit);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) {
    respond(["status"=>"error","message"=>"Invalid business unit"]);
}

$business_unit_id = $row['id'];

// --- insert/update ---
$stmt = $conn->prepare("
REPLACE INTO projects
(id, business_unit_id, name, stage, status, owner, priority,
 gate_due, completion_due, progress, current_update, next_steps)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sissssssssss",
    $id,
    $business_unit_id,
    $name,
    $stage,
    $status,
    $owner,
    $priority,
    $gate_due,
    $completion_due,
    $progress,
    $current_update,
    $next_steps
);

if ($stmt->execute()) {
    respond(["status" => "success"]);
} else {
    respond(["status" => "error", "message" => $stmt->error]);
}

$conn->query("INSERT INTO activity_log
(project_id, action, user) 
VALUES ('$id', 'Project updated', 'system')");

