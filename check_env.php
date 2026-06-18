<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\Environment;
use App\Config\Database;

echo "=== Environment Check ===\n\n";

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Environment Variables:\n";
echo "APP_NAME: " . ($_ENV['APP_NAME'] ?? 'Not set') . "\n";
echo "APP_ENV: " . ($_ENV['APP_ENV'] ?? 'Not set') . "\n";
echo "APP_DEBUG: " . ($_ENV['APP_DEBUG'] ?? 'Not set') . "\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'Not set') . "\n";
echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'Not set') . "\n";
echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'Not set') . "\n";
echo "DB_PASSWORD: " . (isset($_ENV['DB_PASSWORD']) && $_ENV['DB_PASSWORD'] ? '*** Set ***' : 'Empty') . "\n\n";

// Test database connection
echo "Testing database connection...\n";
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "✅ Database connection successful!\n";
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "📊 Users table has " . $row['count'] . " records\n";
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}