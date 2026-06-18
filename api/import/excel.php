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

    // Headers: map to expected columns
    $headers = array_map('trim', $rows[0]);
    $expectedHeaders = ['Business Unit', 'Project', 'Stage', 'Status', 'Owner', 'Priority', 'Gate Due', 'Completion Due', 'Progress', 'Current Update', 'Next Steps'];
    // Validate headers (case-insensitive)
    $headerMap = [];
    foreach ($expectedHeaders as $expected) {
        $found = false;
        foreach ($headers as $idx => $h) {
            if (strtolower(trim($h)) === strtolower($expected)) {
                $headerMap[$expected] = $idx;
                $found = true;
                break;
            }
        }
        if (!$found) {
            throw new Exception("Missing expected column: '$expected'");
        }
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

        // Extract values using header map
        $businessUnit = trim($row[$headerMap['Business Unit']] ?? '');
        $projectName = trim($row[$headerMap['Project']] ?? '');
        $stage = trim($row[$headerMap['Stage']] ?? 'initiation');
        $status = trim($row[$headerMap['Status']] ?? 'ontrack');
        $owner = trim($row[$headerMap['Owner']] ?? '');
        $priority = trim($row[$headerMap['Priority']] ?? 'normal');
        $gateDue = trim($row[$headerMap['Gate Due']] ?? '');
        $completionDue = trim($row[$headerMap['Completion Due']] ?? '');
        $progress = (int)($row[$headerMap['Progress']] ?? 0);
        $currentUpdate = trim($row[$headerMap['Current Update']] ?? '');
        $nextSteps = trim($row[$headerMap['Next Steps']] ?? '');

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

        // Check duplicate (name + business_unit_id)
        $dupKey = $projectName . '|' . $buId;
        if (isset($existingProjects[$dupKey])) {
            $duplicates++;
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

        $insertStmt->bind_param(
            "sissssssssiss",
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
            $user['id']
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