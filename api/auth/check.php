<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

if (isset($_SESSION['user'])) {
    echo json_encode([
        'status' => 'success',
        'user' => $_SESSION['user']
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Not authenticated'
    ]);
}
?>