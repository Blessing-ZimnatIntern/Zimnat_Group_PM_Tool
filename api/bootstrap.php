<?php

// Suppress warnings in CLI mode
if (php_sapi_name() === 'cli') {
    error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
}

// Load composer autoloader if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// Load environment variables
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Start session only if not in CLI mode and session not already started
if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_name($_ENV['SESSION_NAME'] ?? 'zpm_session');
    session_set_cookie_params(
        (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
        '/',
        '',
        ($_ENV['SESSION_SECURE'] ?? 'false') === 'true',
        ($_ENV['SESSION_HTTP_ONLY'] ?? 'true') === 'true'
    );
    session_start();
}

// Set error reporting
if (($_ENV['APP_ENV'] ?? 'development') === 'development' && 
    ($_ENV['APP_DEBUG'] ?? 'true') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone for PHP
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// CORS headers only for web requests (not CLI)
if (php_sapi_name() !== 'cli') {
    // CORS headers for development
    if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }

    // Handle preflight requests
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

define('BASE_PATH', dirname(__DIR__));

// Include database and auth
require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/api/middleware/auth.php';