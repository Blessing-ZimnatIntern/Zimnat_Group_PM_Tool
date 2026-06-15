<?php
require_once '../db.php';
require_once '../utils.php';

session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["error"=>"unauthorized"]);
    exit;
}

$data = jsonInput();

$stmt = $conn->prepare("
INSERT INTO project_updates (project_id, update_text, next_steps)
VALUES (?, ?, ?)
");

$stmt->bind_param(
    "sss",
    $data['project_id'],
    $data['update'],
    $data['next_steps']
);

$stmt->execute();

respond(["status"=>"saved"]);