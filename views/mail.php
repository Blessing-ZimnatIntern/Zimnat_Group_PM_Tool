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
<div class="view-content">
    <h2>Email Log</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Project</th>
                <th>Assignee</th>
                <th>Type</th>
                <th>Status</th>
                <th>Sent At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="5">No email logs found.</td></tr>
            <?php else: foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['project_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($log['assignee_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($log['email_type']) ?></td>
                    <td><span class="status-badge" style="background:<?= $log['status'] === 'sent' ? '#16a34a' : '#dc2626' ?>"><?= $log['status'] ?></span></td>
                    <td><?= htmlspecialchars($log['sent_at']) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>