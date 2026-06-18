<?php
require_once __DIR__ . '/../api/bootstrap.php';
use App\Config\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "
    SELECT e.id, e.email_type, e.status, e.message, e.sent_at,
           p.name AS project_name,
           u.full_name AS assignee_name
    FROM email_logs e
    LEFT JOIN projects p ON e.project_id = p.id
    LEFT JOIN users u ON e.assignee_id = u.id
    ORDER BY e.sent_at DESC
    LIMIT 100
";
$result = $conn->query($sql);
$logs = $result->fetch_all(MYSQLI_ASSOC);
?>
<div class="view-content" style="padding:24px;">
    <h2 style="font-size:20px;font-weight:700;margin-bottom:16px;color:#1a2332;">Email Log</h2>
    <div style="background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="background:#f9fafb;border-bottom:2px solid #e5e7eb;">
                    <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151;">Project</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151;">Assignee</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151;">Type</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151;">Status</th>
                    <th style="padding:12px 16px;text-align:left;font-weight:600;color:#374151;">Sent At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="5" style="padding:20px;text-align:center;color:#6b7280;">No email logs found.</td></tr>
                <?php else: foreach ($logs as $log): ?>
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:12px 16px;font-weight:500;"><?= htmlspecialchars($log['project_name'] ?? 'N/A') ?></td>
                        <td style="padding:12px 16px;color:#4b5563;"><?= htmlspecialchars($log['assignee_name'] ?? 'N/A') ?></td>
                        <td style="padding:12px 16px;color:#4b5563;"><?= htmlspecialchars($log['email_type']) ?></td>
                        <td style="padding:12px 16px;">
                            <span style="background:<?= $log['status'] === 'sent' ? '#16a34a' : '#dc2626' ?>;color:#fff;padding:2px 10px;border-radius:5px;font-size:12px;font-weight:600;">
                                <?= ucfirst($log['status']) ?>
                            </span>
                        </td>
                        <td style="padding:12px 16px;color:#4b5563;"><?= htmlspecialchars($log['sent_at']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>