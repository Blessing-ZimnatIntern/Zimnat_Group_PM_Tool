<?php

function jsonInput() {
    return json_decode(file_get_contents("php://input"), true);
}

function respond($data) {
    header("Content-Type: application/json");
    echo json_encode($data);
    exit;
}