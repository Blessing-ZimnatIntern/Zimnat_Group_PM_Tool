<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../helpers/ExecutiveSummary.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();
$businessUnitId = isset($_GET['business_unit']) ? (int)$_GET['business_unit'] : null;

try {
    $conn = Database::getInstance()->getConnection();
    $result = buildExecutiveSummary($conn, $businessUnitId);
    echo json_encode(['status' => 'success'] + $result);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
