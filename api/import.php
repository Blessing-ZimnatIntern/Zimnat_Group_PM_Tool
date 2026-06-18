<?php
require_once __DIR__ . '/bootstrap.php';
use App\Config\Database;
use App\Middleware\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

try {
    $user = Auth::requireRole('editor');

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error.');
    }

    $file = $_FILES['file']['tmp_name'];
    $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    // Parse file
    if ($extension === 'csv') {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        $reader->setDelimiter(',');
        $reader->setEnclosure('"');
        $reader->setSheetIndex(0);
    } elseif ($extension === 'xls') {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
    } elseif ($extension === 'xlsx') {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    } else {
        throw new Exception('Unsupported file format. Please upload CSV or Excel.');
    }

    $spreadsheet = $reader->load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    if (empty($rows)) {
        throw new Exception('File is empty.');
    }

    // Map headers (case-insensitive)
    $headers = array_map('trim', $rows[0]);
    $expected = ['business unit', 'project', 'stage', 'status', 'owner', 'priority', 'gate due', 'completion due', 'progress', 'current update', 'next steps'];
    $headerMap = [];
    foreach ($expected as $col) {
        $found = false;
        foreach ($headers as $idx => $h) {
            if (strtolower(trim($h)) === $col) {
                $headerMap[$col] = $idx;
                $found = true;
                break;
            }
        }
        if (!$found) {
            throw new Exception("Missing required column: '$col'");
        }
    }

    array_shift($rows); // remove header

    $db = Database::getInstance();
    $conn = $db->getConnection();
    $conn->begin_transaction();

    $imported = 0;
    $errors = [];
    $duplicates = 0;

    $insertStmt = $conn->prepare("
        INSERT INTO projects (id, business_unit_id, name, stage, status, owner, priority, gate_due, completion_due, progress, current_update, next_steps, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $buCache = [];
    $existingProjects = [];

    foreach ($rows as $rowIndex => $row) {
        if (empty(array_filter($row))) continue;

        $businessUnit = trim($row[$headerMap['business unit']] ?? '');
        $projectName = trim($row[$headerMap['project']] ?? '');
        if (empty($businessUnit) || empty($projectName)) {
            $errors[] = "Row " . ($rowIndex + 2) . ": Business unit and project name are required.";
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

        // Check duplicate
        $dupKey = $projectName . '|' . $buId;
        if (isset($existingProjects[$dupKey])) {
            $duplicates++;
            continue;
        }

        // Map values
        $stageMap = [
            'initiation' => 'initiation', 'planning' => 'planning', 'execution' => 'execution',
            'development' => 'execution', 'qa' => 'qa', 'uat' => 'uat', 'closure' => 'closure'
        ];
        $stage = $stageMap[strtolower(trim($row[$headerMap['stage']] ?? ''))] ?? 'initiation';

        $statusMap = [
            'ontrack' => 'ontrack', 'on track' => 'ontrack',
            'atrisk' => 'atrisk', 'at risk' => 'atrisk',
            'behind' => 'behind', 'behind schedule' => 'behind',
            'complete' => 'complete'
        ];
        $status = $statusMap[strtolower(trim($row[$headerMap['status']] ?? ''))] ?? 'ontrack';

        $priorityMap = ['urgent'=>'urgent', 'high'=>'high', 'normal'=>'normal', 'low'=>'low'];
        $priority = $priorityMap[strtolower(trim($row[$headerMap['priority']] ?? ''))] ?? 'normal';

        $owner = trim($row[$headerMap['owner']] ?? '');
        $gateDue = !empty($row[$headerMap['gate due']]) ? date('Y-m-d', strtotime($row[$headerMap['gate due']])) : null;
        $completionDue = !empty($row[$headerMap['completion due']]) ? date('Y-m-d', strtotime($row[$headerMap['completion due']])) : null;
        $progress = min(100, max(0, (int)($row[$headerMap['progress']] ?? 0)));
        $currentUpdate = trim($row[$headerMap['current update']] ?? '');
        $nextSteps = trim($row[$headerMap['next steps']] ?? '');

        $projectId = 'proj_' . bin2hex(random_bytes(16));
        $insertStmt->bind_param(
            "sissssssssiss",
            $projectId, $buId, $projectName, $stage, $status,
            $owner, $priority, $gateDue, $completionDue, $progress,
            $currentUpdate, $nextSteps, $user['id']
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
    if (isset($conn)) $conn->rollback();
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}