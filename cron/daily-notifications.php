#!/usr/bin/php
<?php
// Run daily via cron: 0 8 * * * php /path/to/cron/daily-notifications.php

require_once __DIR__ . '/../api/bootstrap.php';

use App\Config\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$db = Database::getInstance();
$conn = $db->getConnection();

// Find overdue projects that are not complete
$sql = "
    SELECT 
        p.id,
        p.name,
        p.owner,
        p.completion_due,
        b.name AS business_unit,
        u.email AS owner_email
    FROM projects p
    JOIN business_units b ON p.business_unit_id = b.id
    LEFT JOIN users u ON u.email = p.owner OR u.username = p.owner
    WHERE p.status != 'complete'
      AND p.completion_due < CURDATE()
      AND p.deleted_at IS NULL
";
$result = $conn->query($sql);
$overdue = $result->fetch_all(MYSQLI_ASSOC);

if (empty($overdue)) {
    echo "No overdue projects.\n";
    exit;
}

// PHPMailer (ensure installed via composer)
require_once __DIR__ . '/../vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.office365.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USER'] ?? '';
    $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $_ENV['SMTP_PORT'] ?? 587;

    $mail->setFrom($_ENV['SMTP_FROM'] ?? 'noreply@zimnat.com', 'Zimnat PM');
    $mail->addAddress($_ENV['ADMIN_EMAIL'] ?? 'admin@zimnat.com');
    $mail->isHTML(true);
    $mail->Subject = 'Daily Overdue Projects Report - ' . date('Y-m-d');

    $body = '<h2>Overdue Projects as of ' . date('Y-m-d') . '</h2>';
    $body .= '<table border="1" cellpadding="5"><tr><th>Project</th><th>Business Unit</th><th>Owner</th><th>Completion Due</th></tr>';
    foreach ($overdue as $p) {
        $body .= sprintf(
            '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
            htmlspecialchars($p['name']),
            htmlspecialchars($p['business_unit']),
            htmlspecialchars($p['owner']),
            $p['completion_due']
        );
    }
    $body .= '</table>';
    $mail->Body = $body;

    $mail->send();
    echo "Notification sent to " . $_ENV['ADMIN_EMAIL'] . "\n";
} catch (Exception $e) {
    echo "Mail error: {$mail->ErrorInfo}\n";
}