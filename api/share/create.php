<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

$user = Auth::requireAuth();

$data = json_decode(file_get_contents('php://input'), true);
$reportType = $data['report_type'] ?? 'portfolio';
$filters = $data['filters'] ?? null;
$expires_in = $data['expires_in'] ?? 7;

$db = Database::getInstance();
$conn = $db->getConnection();

// Ensure table exists
$conn->query("CREATE TABLE IF NOT EXISTS shared_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,
    report_type ENUM('portfolio','unit','custom') DEFAULT 'portfolio',
    filters JSON,
    expires_at TIMESTAMP NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
)");

$token = bin2hex(random_bytes(32));
$expires_at = date('Y-m-d H:i:s', strtotime("+$expires_in days"));

$stmt = $conn->prepare("INSERT INTO shared_reports (token, report_type, filters, expires_at, created_by) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssssi", $token, $reportType, json_encode($filters), $expires_at, $user['id']);
$stmt->execute();

$shareUrl = ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST'] . '/share.php?token=' . $token;

echo json_encode([
    'status' => 'success',
    'token' => $token,
    'url' => $shareUrl,
    'expires_at' => $expires_at
]);