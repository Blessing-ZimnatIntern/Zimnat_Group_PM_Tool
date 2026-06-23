#!/usr/bin/php
<?php
// Run weekly via cron/Task Scheduler: 0 8 * * 1 php /path/to/cron/weekly-digest.php
// Dry run (prints the email instead of sending it): php cron/weekly-digest.php --dry-run

require_once __DIR__ . '/../api/bootstrap.php';
require_once __DIR__ . '/../api/helpers/ExecutiveSummary.php';

use App\Config\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dryRun = in_array('--dry-run', $argv ?? [], true);

$db = Database::getInstance();
$conn = $db->getConnection();

$summaryResult = buildExecutiveSummary($conn, null);
$summary = $summaryResult['summary'];
$stats = $summaryResult['stats'];

$attentionStmt = $conn->query("
    SELECT p.id, p.name, p.owner, p.completion_due,
           b.name AS business_unit,
           CASE WHEN p.status != 'complete' AND p.completion_due < CURDATE() THEN 'overdue' ELSE p.status END AS effective_status
    FROM projects p
    JOIN business_units b ON p.business_unit_id = b.id
    WHERE p.deleted_at IS NULL
      AND (
          (p.status != 'complete' AND p.completion_due < CURDATE())
          OR p.status IN ('atrisk', 'behind')
      )
    ORDER BY (p.status != 'complete' AND p.completion_due < CURDATE()) DESC, p.completion_due ASC
    LIMIT 15
");
$needsAttention = $attentionStmt->fetch_all(MYSQLI_ASSOC);

$statusLabels = ['ontrack' => 'On Track', 'atrisk' => 'At Risk', 'behind' => 'Behind Schedule', 'overdue' => 'Overdue', 'complete' => 'Complete'];
$statusColors = ['ontrack' => '#16a34a', 'atrisk' => '#f59e0b', 'behind' => '#9ca3af', 'overdue' => '#dc2626', 'complete' => '#3b82f6'];

ob_start();
?>
<div style="font-family:Arial,sans-serif;max-width:680px;margin:0 auto;color:#1a2332;">
    <h2 style="color:#1a4a7a;">Weekly Portfolio Digest — <?= date('Y-m-d') ?></h2>

    <h3 style="margin-top:24px;">Executive Summary</h3>
    <p style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:14px 18px;line-height:1.6;font-size:14px;"><?= htmlspecialchars($summary) ?></p>

    <h3 style="margin-top:24px;">Key Stats</h3>
    <table cellpadding="8" style="border-collapse:collapse;width:100%;font-size:13px;">
        <tr style="background:#f9fafb;">
            <td><b>Total Projects</b></td><td><?= $stats['total'] ?></td>
            <td><b>Avg Progress</b></td><td><?= $stats['avg_progress'] ?>%</td>
        </tr>
        <tr>
            <td><b>On Track</b></td><td><?= $stats['counts']['ontrack'] ?></td>
            <td><b>Complete</b></td><td><?= $stats['counts']['complete'] ?></td>
        </tr>
        <tr style="background:#f9fafb;">
            <td><b>At Risk</b></td><td><?= $stats['counts']['atrisk'] ?></td>
            <td><b>Behind Schedule</b></td><td><?= $stats['counts']['behind'] ?></td>
        </tr>
        <tr>
            <td><b>Overdue</b></td><td style="color:#dc2626;font-weight:bold;"><?= $stats['counts']['overdue'] ?></td>
            <td><b>Over-allocated Staff</b></td><td><?= $stats['overallocated_count'] ?></td>
        </tr>
        <tr style="background:#f9fafb;">
            <td><b>Total Budget</b></td><td>$<?= number_format($stats['total_budget'], 0) ?></td>
            <td><b>Total Actual Cost</b></td><td>$<?= number_format($stats['total_actual'], 0) ?></td>
        </tr>
    </table>

    <h3 style="margin-top:24px;">Needs Attention (<?= count($needsAttention) ?>)</h3>
    <?php if (empty($needsAttention)): ?>
        <p style="color:#6b7280;">Nothing flagged this week.</p>
    <?php else: ?>
    <table cellpadding="8" style="border-collapse:collapse;width:100%;font-size:13px;">
        <tr style="background:#f9fafb;border-bottom:2px solid #e5e7eb;">
            <th style="text-align:left;">Project</th><th style="text-align:left;">Business Unit</th><th style="text-align:left;">Owner</th><th style="text-align:left;">Status</th><th style="text-align:left;">Completion Due</th>
        </tr>
        <?php foreach ($needsAttention as $p): ?>
        <tr style="border-bottom:1px solid #f3f4f6;">
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['business_unit']) ?></td>
            <td><?= htmlspecialchars($p['owner'] ?: '-') ?></td>
            <td><span style="background:<?= $statusColors[$p['effective_status']] ?? '#6b7280' ?>;color:#fff;padding:2px 8px;border-radius:4px;font-size:11px;"><?= $statusLabels[$p['effective_status']] ?? ucfirst($p['effective_status']) ?></span></td>
            <td><?= htmlspecialchars($p['completion_due'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <p style="margin-top:24px;color:#9aa8b9;font-size:12px;">Automated weekly digest from the Z Group Project Management Portal.</p>
</div>
<?php
$htmlBody = ob_get_clean();

if ($dryRun) {
    echo "=== DRY RUN — email not sent ===\n\n";
    echo strip_tags(str_replace(['<tr', '</table>', '<h2', '<h3'], ["\n<tr", "</table>\n", "\n<h2", "\n<h3"], $htmlBody)) . "\n";
    echo "\n=== Recipients would be: " . ($_ENV['DIGEST_RECIPIENTS'] ?? $_ENV['ADMIN_EMAIL'] ?? '(none configured)') . " ===\n";
    exit;
}

$recipients = array_filter(array_map('trim', explode(',', $_ENV['DIGEST_RECIPIENTS'] ?? $_ENV['ADMIN_EMAIL'] ?? '')));
if (empty($recipients)) {
    echo "No DIGEST_RECIPIENTS or ADMIN_EMAIL configured — nothing to send.\n";
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'] ?? 'smtp.office365.com';
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USER'] ?? '';
    $mail->Password = $_ENV['SMTP_PASS'] ?? '';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $_ENV['SMTP_PORT'] ?? 587;

    $mail->setFrom($_ENV['SMTP_FROM'] ?? 'noreply@zimnat.com', 'Zimnat PM');
    foreach ($recipients as $email) {
        $mail->addAddress($email);
    }
    $mail->isHTML(true);
    $mail->Subject = 'Weekly Portfolio Digest — ' . date('Y-m-d');
    $mail->Body = $htmlBody;

    $mail->send();
    echo "Weekly digest sent to: " . implode(', ', $recipients) . "\n";
} catch (Exception $e) {
    echo "Mail error: {$mail->ErrorInfo}\n";
    exit(1);
}
