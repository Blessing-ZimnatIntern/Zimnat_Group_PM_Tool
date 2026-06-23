<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
use App\Middleware\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

try {
    $user = Auth::requireRole('editor');

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }

    $file = $_FILES['file']['tmp_name'];
    $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    // Load the spreadsheet
    if ($extension === 'csv') {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        $reader->setDelimiter(',');
        $reader->setEnclosure('"');
        $reader->setSheetIndex(0);
    } elseif ($extension === 'xls') {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    } else {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    }

    $spreadsheet = $reader->load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    if (empty($rows)) {
        throw new Exception('File is empty');
    }

    // Headers: map flexibly to expected columns (aliases accepted, missing columns import as blank)
    $headers = array_map('trim', $rows[0]);
    $headerAliases = [
        'Business Unit' => ['business unit'],
        'Project' => ['project', 'project name'],
        'Stage' => ['stage', 'stage gate'],
        'Status' => ['status'],
        'Owner' => ['owner'],
        'Priority' => ['priority'],
        'Gate Due' => ['gate due', 'stage gate due date', 'gate due date'],
        'Completion Due' => ['completion due', 'completion date', 'project completion date'],
        'Progress' => ['progress', 'progress %', 'progress%'],
        'Current Update' => ['current update', 'current update / comment', 'update'],
        'Next Steps' => ['next steps'],
    ];
    $headerMap = [];
    foreach ($headerAliases as $expected => $aliases) {
        foreach ($headers as $idx => $h) {
            if (in_array(strtolower(trim($h)), $aliases, true)) {
                $headerMap[$expected] = $idx;
                break;
            }
        }
        // Column not found in sheet — leave unmapped; values for it will import as blank.
    }
    if (!isset($headerMap['Business Unit']) || !isset($headerMap['Project'])) {
        throw new Exception("Sheet must include at least a 'Business Unit' and 'Project' column.");
    }

    // Remove header row
    array_shift($rows);

    $db = Database::getInstance();
    $conn = $db->getConnection();
    $conn->begin_transaction();

    $imported = 0;
    $errors = [];
    $duplicates = 0;

    // Prepare insert statement
    $insertStmt = $conn->prepare("
        INSERT INTO projects (id, business_unit_id, name, stage, status, owner, priority, gate_due, completion_due, progress, current_update, next_steps, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // Cache business unit IDs
    $buCache = [];
    $existingProjects = [];

    foreach ($rows as $rowIndex => $row) {
        if (empty(array_filter($row))) continue;

        // Extract values using header map; columns absent from the sheet import as blank, never invented
        $col = function($name, $default = '') use ($row, $headerMap) {
            return isset($headerMap[$name]) ? trim($row[$headerMap[$name]] ?? '') : $default;
        };
        $businessUnit = $col('Business Unit');
        $projectName = $col('Project');
        $stage = $col('Stage', 'initiation');
        $status = $col('Status', 'ontrack');
        $owner = $col('Owner');
        $priority = $col('Priority', 'normal');
        $gateDue = $col('Gate Due');
        $completionDue = $col('Completion Due');
        $progress = (int)$col('Progress', 0);
        $currentUpdate = $col('Current Update');
        $nextSteps = $col('Next Steps');

        if (empty($businessUnit) || empty($projectName)) {
            $errors[] = "Row " . ($rowIndex + 2) . ": Business Unit and Project are required.";
            continue;
        }

        // Get business unit ID
        $buKey = strtolower($businessUnit);
        if (!isset($buCache[$buKey])) {
            $buStmt = $conn->prepare("SELECT id FROM business_units WHERE LOWER(name) = ?");
            $buStmt->bind_param("s", $buKey);
            $buStmt->execute();
            $res = $buStmt->get_result();
            if ($res->num_rows === 0) {
                $errors[] = "Row " . ($rowIndex + 2) . ": Business unit '$businessUnit' not found.";
                continue;
            }
            $buCache[$buKey] = $res->fetch_assoc()['id'];
        }
        $buId = $buCache[$buKey];

        // Check duplicate (name + business_unit_id) — both within this batch and against existing rows
        $dupKey = $projectName . '|' . $buId;
        if (isset($existingProjects[$dupKey])) {
            $duplicates++;
            continue;
        }
        $existsStmt = $conn->prepare("SELECT 1 FROM projects WHERE name = ? AND business_unit_id = ? AND deleted_at IS NULL LIMIT 1");
        $existsStmt->bind_param("si", $projectName, $buId);
        $existsStmt->execute();
        if ($existsStmt->get_result()->num_rows > 0) {
            $duplicates++;
            $existingProjects[$dupKey] = true;
            continue;
        }

        // Map values to internal enums
        $stageMap = [
            'initiation' => 'initiation', 'planning' => 'planning', 'execution' => 'execution',
            'development' => 'execution', 'execution/development' => 'execution',
            'qa' => 'qa', 'quality assurance' => 'qa',
            'uat' => 'uat', 'user acceptance testing' => 'uat',
            'closure' => 'closure', 'project closure' => 'closure'
        ];
        $stage = $stageMap[strtolower($stage)] ?? 'initiation';

        $statusMap = [
            'ontrack' => 'ontrack', 'on track' => 'ontrack',
            'atrisk' => 'atrisk', 'at risk' => 'atrisk',
            'behind' => 'behind', 'behind schedule' => 'behind',
            'complete' => 'complete'
        ];
        $status = $statusMap[strtolower($status)] ?? 'ontrack';

        $priorityMap = ['urgent'=>'urgent', 'high'=>'high', 'normal'=>'normal', 'low'=>'low'];
        $priority = $priorityMap[strtolower($priority)] ?? 'normal';

        $gateDueDate = !empty($gateDue) ? date('Y-m-d', strtotime($gateDue)) : null;
        $completionDueDate = !empty($completionDue) ? date('Y-m-d', strtotime($completionDue)) : null;
        $progress = max(0, min(100, $progress));

        // Generate ID
        $projectId = 'proj_' . bin2hex(random_bytes(16));

        // Types must align 1:1 with the INSERT column order above:
        // id(s) business_unit_id(i) name(s) stage(s) status(s) owner(s) priority(s)
        // gate_due(s) completion_due(s) progress(i) current_update(s) next_steps(s) created_by(i)
        $bindTypes = 's' . 'i' . str_repeat('s', 7) . 'i' . 'ss' . 'i';
        $createdBy = (int)$user['id'];
        $insertStmt->bind_param(
            $bindTypes,
            $projectId,
            $buId,
            $projectName,
            $stage,
            $status,
            $owner,
            $priority,
            $gateDueDate,
            $completionDueDate,
            $progress,
            $currentUpdate,
            $nextSteps,
            $createdBy
        );

        if ($insertStmt->execute()) {
            $imported++;
            $existingProjects[$dupKey] = true;
        } else {
            $errors[] = "Row " . ($rowIndex + 2) . ": " . $insertStmt->error;
        }
    }

    if (!empty($errors)) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'imported' => 0, 'errors' => $errors]);
    } else {
        $conn->commit();
        echo json_encode([
            'status' => 'success',
            'imported' => $imported,
            'duplicates' => $duplicates,
            'message' => "Imported $imported projects" . ($duplicates ? " ($duplicates duplicates skipped)" : "")
        ]);
    }
} catch (Exception $e) {
    if (isset($conn) && $conn) $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}