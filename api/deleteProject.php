<?php

session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["error"=>"unauthorized"]);
    exit;
}

require_once '../db.php';
require_once '../utils.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    respond(["status"=>"error","message"=>"Missing ID"]);
}

$stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
$stmt->bind_param("s", $id);

if ($stmt->execute()) {
    respond(["status"=>"deleted"]);
} else {
    respond(["status"=>"error"]);
}