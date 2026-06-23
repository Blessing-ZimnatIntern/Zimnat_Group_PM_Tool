<?php

function notifyUser($conn, $userId, $projectId, $type, $message, $relatedUserId = null) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, project_id, type, message, related_user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isssi", $userId, $projectId, $type, $message, $relatedUserId);
    $stmt->execute();
}

function logAllocation($conn, $projectId, $ownerId, $action, $reason = null, $actorId = null) {
    $stmt = $conn->prepare("INSERT INTO project_allocation_log (project_id, owner_id, action, reason, actor_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sissi", $projectId, $ownerId, $action, $reason, $actorId);
    $stmt->execute();
}

// Notifies a newly (re)assigned owner and resets the acceptance status to pending.
function notifyNewOwner($conn, $projectId, $ownerId, $projectName, $actorId = null) {
    if (!$ownerId) return;
    $resetStmt = $conn->prepare("UPDATE projects SET owner_acceptance_status = 'pending', owner_rejection_reason = NULL WHERE id = ?");
    $resetStmt->bind_param("s", $projectId);
    $resetStmt->execute();

    logAllocation($conn, $projectId, $ownerId, 'assigned', null, $actorId);
    notifyUser(
        $conn,
        $ownerId,
        $projectId,
        'project_assigned',
        "You've been assigned to \"$projectName\". Review the requirements and start working, or reject it with a reason.",
        $actorId
    );
}

// Notifies every assignee (allocator) on a project, e.g. when the owner rejects it.
function notifyAssignees($conn, $projectId, $type, $message, $relatedUserId = null) {
    $stmt = $conn->prepare("SELECT DISTINCT user_id FROM project_assignees WHERE project_id = ?");
    $stmt->bind_param("s", $projectId);
    $stmt->execute();
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
        notifyUser($conn, $row['user_id'], $projectId, $type, $message, $relatedUserId);
    }
}
