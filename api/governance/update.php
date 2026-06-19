<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../helpers/ProjectHelper.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireRole('editor');
$data = json_decode(file_get_contents('php://input'), true) ?: [];

$projectId = $data['project_id'] ?? '';
$itemKey = $data['item_key'] ?? '';
$status = $data['status'] ?? '';

$validStatuses = ['pending', 'progress', 'signed', 'na'];
if (!$projectId || !$itemKey || !in_array($status, $validStatuses, true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'Project ID, item key, and valid status are required']);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();
    $conn->begin_transaction();

    // 1. Validate project
    $projectStmt = $conn->prepare("SELECT id, stage FROM projects WHERE id = ?");
    $projectStmt->bind_param("s", $projectId);
    $projectStmt->execute();
    $project = $projectStmt->get_result()->fetch_assoc();
    if (!$project) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'error' => 'Project not found']);
        exit;
    }

    // 2. Get template details
    $templateStmt = $conn->prepare("
        SELECT item_label, stage_gate
        FROM governance_items
        WHERE project_id IS NULL AND item_key = ?
        LIMIT 1
    ");
    $templateStmt->bind_param("s", $itemKey);
    $templateStmt->execute();
    $template = $templateStmt->get_result()->fetch_assoc() ?: ['item_label' => $itemKey, 'stage_gate' => null];

    // 3. Upsert governance item
    $upsert = $conn->prepare("
        INSERT INTO governance_items (project_id, item_key, item_label, stage_gate, status, updated_by, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            item_label = VALUES(item_label),
            stage_gate = VALUES(stage_gate),
            updated_by = VALUES(updated_by),
            updated_at = NOW()
    ");
    $upsert->bind_param(
        "sssssi",
        $projectId,
        $itemKey,
        $template['item_label'],
        $template['stage_gate'],
        $status,
        $user['id']
    );
    $upsert->execute();

    // 4. 🔥 NEW: Recalculate and update project progress
    $newProgress = calculateAutoProgress($project['stage'], $conn, $projectId);
    $progressStmt = $conn->prepare("UPDATE projects SET progress = ?, updated_by = ?, updated_at = NOW() WHERE id = ?");
    $progressStmt->bind_param("iis", $newProgress, $user['id'], $projectId);
    $progressStmt->execute();

    // 5. Update governance completion percent
    $pctStmt = $conn->prepare("
        SELECT
            SUM(CASE WHEN status != 'na' THEN 1 ELSE 0 END) AS applicable,
            SUM(CASE WHEN status = 'signed' THEN 1 ELSE 0 END) AS signed_count
        FROM governance_items
        WHERE project_id = ?
    ");
    $pctStmt->bind_param("s", $projectId);
    $pctStmt->execute();
    $counts = $pctStmt->get_result()->fetch_assoc();
    $applicable = (int)($counts['applicable'] ?? 0);
    $signed = (int)($counts['signed_count'] ?? 0);
    $completion = $applicable > 0 ? (int)round(($signed / $applicable) * 100) : 100;

    $colStmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'projects'
          AND COLUMN_NAME = 'governance_completion_percent'
    ");
    $colStmt->execute();
    if (((int)$colStmt->get_result()->fetch_assoc()['total']) > 0) {
        $projectUpdate = $conn->prepare("UPDATE projects SET governance_completion_percent = ?, updated_by = ?, updated_at = NOW() WHERE id = ?");
        $projectUpdate->bind_param("iis", $completion, $user['id'], $projectId);
        $projectUpdate->execute();
    }

    // 6. Log activity
    $logStmt = $conn->prepare("
        INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent)
        VALUES (?, 'governance_update', 'project', ?, ?, ?, ?)
    ");
    $details = json_encode(['item_key' => $itemKey, 'status' => $status, 'governance_completion_percent' => $completion, 'progress' => $newProgress]);
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $logStmt->bind_param("issss", $user['id'], $projectId, $details, $ip, $userAgent);
    $logStmt->execute();

    $conn->commit();
    echo json_encode([
        'status' => 'success',
        'project_id' => $projectId,
        'item_key' => $itemKey,
        'governance_completion_percent' => $completion,
        'auto_progress' => $newProgress
    ]);
} catch (Throwable $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}