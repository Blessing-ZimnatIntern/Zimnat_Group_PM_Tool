<?php
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireRole('editor');
$data = json_decode(file_get_contents('php://input'), true) ?: [];

$id = (int)($data['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'id is required']);
    exit;
}

try {
    $conn = Database::getInstance()->getConnection();

    $fields = [];
    $params = [];
    $types = '';
    foreach (['name' => 's', 'role_title' => 's', 'notes' => 's'] as $field => $type) {
        if (array_key_exists($field, $data)) {
            $fields[] = "$field = ?";
            $params[] = trim($data[$field]);
            $types .= $type;
        }
    }
    if (empty($fields)) {
        echo json_encode(['status' => 'success', 'message' => 'No changes']);
        exit;
    }
    $params[] = $id;
    $types .= 'i';

    $stmt = $conn->prepare("UPDATE stakeholders SET " . implode(', ', $fields) . " WHERE id = ?");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();

    echo json_encode(['status' => 'success']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}
