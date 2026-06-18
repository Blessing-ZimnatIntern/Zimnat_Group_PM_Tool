<?php
// Load bootstrap
require_once __DIR__ . '/../bootstrap.php';

use App\Config\Database;

header('Content-Type: application/json');

// Get input data
$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$full_name = $data['full_name'] ?? '';

// Validate input
if (empty($username) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

$minPasswordLength = (int)($_ENV['MIN_PASSWORD_LENGTH'] ?? 8);
if (strlen($password) < $minPasswordLength) {
    http_response_code(400);
    echo json_encode(['error' => "Password must be at least {$minPasswordLength} characters"]);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Check if user already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $checkResult = $check->get_result();
    
    if ($checkResult->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'User with this email or username already exists']);
        exit;
    }
    
    // Hash password with configurable rounds
    $rounds = (int)($_ENV['BCRYPT_ROUNDS'] ?? 12);
    $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => $rounds]);
    
    // Determine role (first user becomes admin)
    $roleCheck = $conn->query("SELECT COUNT(*) as count FROM users");
    $userCount = $roleCheck->fetch_assoc()['count'];
    $role = ($userCount == 0) ? 'admin' : 'viewer';
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $password_hash, $full_name, $role);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'User created successfully',
            'role' => $role
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Registration failed: ' . $stmt->error]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
}