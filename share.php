<?php
require_once 'api/bootstrap.php';

use App\Config\Database;

$token = $_GET['token'] ?? '';

if (!$token) {
    die('Missing token.');
}

$db = Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    SELECT * FROM shared_reports 
    WHERE token = ? AND (expires_at IS NULL OR expires_at > NOW())
");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$share = $result->fetch_assoc();

if (!$share) {
    die('Invalid or expired share link.');
}

// For simplicity, we just display a read-only version of the portfolio.
// In a real implementation, you'd render a clean dashboard with the filters applied.
?>
<!DOCTYPE html>
<html>
<head>
    <title>Shared Report – Zimnat</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; }
        h1 { color: #1a4a7a; }
        .note { color: #6b7a8f; }
    </style>
</head>
<body>
<div class="container">
    <h1>📊 Shared Project Report</h1>
    <p class="note">This is a read-only view. Expires: <?= htmlspecialchars($share['expires_at'] ?? 'Never') ?></p>
    <p><strong>Report Type:</strong> <?= htmlspecialchars($share['report_type']) ?></p>
    <p><strong>Filters applied:</strong> <pre><?= htmlspecialchars(json_encode(json_decode($share['filters']), JSON_PRETTY_PRINT)) ?></pre></p>
    <p><em>In production, this would display the actual dashboard with the filters applied.</em></p>
</div>
</body>
</html>