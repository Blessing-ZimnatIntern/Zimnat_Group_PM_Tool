<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../helpers/Notifications.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();
$data = json_decode(file_get_contents('php://input'), true) ?: [];

$projectId = $data['project_id'] ?? '';
$body = trim($data['body'] ?? '');

if (!$projectId || $body === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'project_id and a non-empty body are required']);
    exit;
}
if (mb_strlen($body) > 4000) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'Comment is too long (max 4000 characters)']);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();

    $projectStmt = $conn->prepare("SELECT id, name FROM projects WHERE id = ?");
    $projectStmt->bind_param("s", $projectId);
    $projectStmt->execute();
    $project = $projectStmt->get_result()->fetch_assoc();
    if (!$project) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'error' => 'Project not found']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO comments (project_id, user_id, body) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $projectId, $user['id'], $body);
    $stmt->execute();
    $commentId = $stmt->insert_id;

    $logStmt = $conn->prepare("
        INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
        VALUES (?, 'comment', 'project', ?, ?, ?, ?)
    ");
    $details = json_encode(['comment_id' => $commentId]);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $logStmt->bind_param("issss", $user['id'], $projectId, $details, $ip, $ua);
    $logStmt->execute();

    // @mentions notify the mentioned user(s), excluding the commenter mentioning themself
    $userStmt = $conn->prepare("SELECT id, full_name FROM users WHERE is_active = 1 AND id != ?");
    $userStmt->bind_param("i", $user['id']);
    $userStmt->execute();
    $snippet = mb_strlen($body) > 140 ? mb_substr($body, 0, 140) . '…' : $body;
    foreach ($userStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $candidate) {
        if (mb_stripos($body, '@' . $candidate['full_name']) !== false) {
            notifyUser(
                $conn,
                $candidate['id'],
                $projectId,
                'comment_mention',
                "{$user['full_name']} mentioned you in a comment on \"{$project['name']}\": \"$snippet\"",
                $user['id']
            );
        }
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'id' => $commentId,
            'project_id' => $projectId,
            'user_id' => $user['id'],
            'full_name' => $user['full_name'] ?? $user['username'],
            'body' => $body,
            'created_at' => date('Y-m-d H:i:s'),
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
