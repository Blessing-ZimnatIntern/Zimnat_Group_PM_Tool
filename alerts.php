<?php
require '../db.php';

$result = $conn->query("
SELECT name, owner, completion_due
FROM projects
WHERE completion_due < CURDATE()
AND status != 'complete'
");

$alerts = [];

while ($row = $result->fetch_assoc()) {
    $alerts[] = $row;
}

echo json_encode($alerts);