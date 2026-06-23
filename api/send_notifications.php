#!/usr/bin/php
<?php
require_once __DIR__ . '/bootstrap.php';
use App\Config\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = Database::getInstance();
$conn = $db->getConnection();

// Find projects that are overdue or at risk and have an assignee with email
$sql = "
    SELECT
        p.id, p.name,
        CASE WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue' ELSE p.status END AS status,
        p.owner, p.assignee_id, p.completion_due,
        u.email AS assignee_email,
        u.full_name AS assignee_name,
        b.name AS business_unit
    FROM projects p
    LEFT JOIN users u ON p.assignee_id = u.id
    JOIN business_units b ON p.business_unit_id = b.id
    WHERE p.deleted_at IS NULL
      AND (p.status = 'atrisk' OR (p.status != 'complete' AND p.completion_due < CURDATE()))
      AND (p.email_sent = 0 OR p.email_sent IS NULL)
      AND u.email IS NOT NULL
";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "No notifications to send.\n";
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.office365.com';
$mail->SMTPAuth   = true;
$mail->Username   = $_ENV['SMTP_USER'] ?? '';
$mail->Password   = $_ENV['SMTP_PASS'] ?? '';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = $_ENV['SMTP_PORT'] ?? 587;
$mail->setFrom($_ENV['SMTP_FROM'] ?? 'noreply@zimnat.com', 'Zimnat PM');

$logStmt = $conn->prepare("INSERT INTO email_logs (project_id, assignee_id, email_type, status, message) VALUES (?, ?, ?, ?, ?)");

while ($row = $result->fetch_assoc()) {
    try {
        $mail->clearAddresses();
        $mail->addAddress($row['assignee_email'], $row['assignee_name']);

        $subject = ($row['status'] === 'overdue') ? '⚠️ Project Overdue' : '⚠️ Project At Risk';
        $mail->Subject = $subject . ': ' . $row['name'];
        $body = "<h2>Project: {$row['name']}</h2>";
        $body .= "<p><strong>Status:</strong> " . ucfirst($row['status']) . "</p>";
        $body .= "<p><strong>Business Unit:</strong> {$row['business_unit']}</p>";
        $body .= "<p><strong>Completion Due:</strong> {$row['completion_due']}</p>";
        $body .= "<p><strong>Owner:</strong> {$row['owner']}</p>";
        $body .= "<p>Please take necessary action.</p>";
        $mail->Body = $body;
        $mail->send();

        $update = $conn->prepare("UPDATE projects SET email_sent = 1 WHERE id = ?");
        $update->bind_param("s", $row['id']);
        $update->execute();

        $logStmt->bind_param("siss", $row['id'], $row['assignee_id'], $row['status'], 'sent', $body);
        $logStmt->execute();

        echo "Email sent to {$row['assignee_email']} for project {$row['name']}\n";
    } catch (Exception $e) {
        $logStmt->bind_param("siss", $row['id'], $row['assignee_id'], $row['status'], 'failed', $mail->ErrorInfo);
        $logStmt->execute();
        echo "Failed to send to {$row['assignee_email']}: {$mail->ErrorInfo}\n";
    }
}