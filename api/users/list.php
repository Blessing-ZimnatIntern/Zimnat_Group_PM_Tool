<?php
require_once __DIR__ . '/../bootstrap.php';
use App\Config\Database;
use App\Middleware\Auth;

header('Content-Type: application/json');

$user = Auth::requireAuth();

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Optional ?roles=editor,admin to restrict the list (e.g. for assignee/allocator pickers)
    $rolesParam = isset($_GET['roles']) ? array_filter(array_map('trim', explode(',', $_GET['roles']))) : [];
    $allowedRoles = array_intersect($rolesParam, ['admin', 'editor', 'viewer']);

    $sql = "SELECT id, full_name, email, role FROM users WHERE is_active = 1";
    $params = [];
    $types = '';
    if (!empty($allowedRoles)) {
        $placeholders = implode(',', array_fill(0, count($allowedRoles), '?'));
        $sql .= " AND role IN ($placeholders)";
        $params = array_values($allowedRoles);
        $types = str_repeat('s', count($allowedRoles));
    }
    $sql .= " ORDER BY full_name";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $users]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => $e->getMessage()]);
}