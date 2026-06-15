<?php
session_start();
require '../db.php';

session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["error"=>"unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'];

$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();

$res = $stmt->get_result()->fetch_assoc();

if ($res) {
    $_SESSION['user'] = $res;
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode(["status"=>"error"]);
}