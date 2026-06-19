<?php
// api/helpers/ProjectHelper.php

function calculateAutoProgress($stage, $conn, $projectId) {
    $stageIndex = [
        'initiation' => 1,
        'planning'   => 2,
        'execution'  => 3,
        'qa'         => 4,
        'uat'        => 5,
        'closure'    => 6
    ];
    
    $base = ($stageIndex[$stage] - 1) / 5 * 100;

    // Fetch governance items for this project
    $stmt = $conn->prepare("SELECT status, stage_gate FROM governance_items WHERE project_id = ?");
    $stmt->bind_param("s", $projectId);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);

    $currentStageItems = array_filter($items, function($g) use ($stage) {
        return $g['stage_gate'] === $stage;
    });
    $total = count($currentStageItems);
    $signed = count(array_filter($currentStageItems, function($g) {
        return $g['status'] === 'signed';
    }));
    $bonus = ($total > 0) ? ($signed / $total) * 15 : 0; // max 15% bonus

    return min(100, round($base + $bonus));
}