<?php

session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["error"=>"unauthorized"]);
    exit;
}

require_once '../db.php';
require_once '../utils.php';

$unit = $_GET['unit'] ?? null;

$sql = "
SELECT 
    p.*,
    b.name AS business_unit
FROM projects p
JOIN business_units b ON p.business_unit_id = b.id
";

if ($unit) {
    $stmt = $conn->prepare(
        $sql . " WHERE b.name = ? ORDER BY p.created_at DESC"
    );
    $stmt->bind_param("s", $unit);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql . " ORDER BY p.created_at DESC");
}

$projects = [];

while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

respond([
    "status" => "success",
    "data" => $projects
]);

$status = $_GET['status'] ?? null;
$owner = $_GET['owner'] ?? null;

$where = [];

if ($status) $where[] = "p.status='$status'";
if ($owner) $where[] = "p.owner='$owner'";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}