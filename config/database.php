<?php

namespace App\Config;

use Dotenv\Dotenv;

class Database {
    private static $instance = null;
    private $connection;
    private $config;
    private $queryLog = [];
    private $queryCount = 0;
    private $slowQueryThreshold = 0.5; // seconds
    
    private function __construct() {
        $this->loadEnvironment();
        $this->config = $this->getConfig();
        $this->connect();
    }
    
    private function loadEnvironment() {
        if (!isset($_ENV['DB_HOST'])) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
            
            // Validate required variables
            $this->validateEnvironment();
        }
    }
    
    private function validateEnvironment() {
        $required = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
        $missing = [];
        
        foreach ($required as $key) {
            if (!isset($_ENV[$key]) || empty($_ENV[$key])) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            throw new \Exception('Missing required environment variables: ' . implode(', ', $missing));
        }
    }
    
    private function getConfig() {
        return [
            'host' => $_ENV['DB_HOST'],
            'port' => (int)($_ENV['DB_PORT'] ?? 3306),
            'database' => $_ENV['DB_DATABASE'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC'
        ];
    }
    
    private function connect() {
        $startTime = microtime(true);
        
        $this->connection = new \mysqli(
            $this->config['host'],
            $this->config['username'],
            $this->config['password'],
            $this->config['database'],
            $this->config['port']
        );
        
        $connectTime = microtime(true) - $startTime;
        
        if ($this->connection->connect_error) {
            $this->handleConnectionError();
        }
        
        // Set charset
        $this->connection->set_charset($this->config['charset']);
        
        // Set timezone – try, but fall back to system time zone if fails
        try {
            $timezone = $this->config['timezone'];
            $result = $this->connection->query("SET time_zone = '$timezone'");
            if (!$result) {
                // If setting time zone fails, log and continue
                error_log("Warning: Could not set time zone to '$timezone'. Using system time zone.");
            }
        } catch (\Exception $e) {
            error_log("Warning: Could not set time zone: " . $e->getMessage());
        }
        
        // Set names and SQL mode
        $this->connection->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        $this->connection->query("SET sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
        
        if ($connectTime > 0.5 && isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
            error_log("Database connection took {$connectTime} seconds");
        }
    }
    
    private function handleConnectionError() {
        $error = [
            'error' => 'Database connection failed',
            'code' => $this->connection->connect_errno,
            'message' => $this->connection->connect_error
        ];
        
        if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development' && 
            isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
            die(json_encode($error, JSON_PRETTY_PRINT));
        }
        
        die(json_encode(['error' => 'Database connection failed']));
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        if (!$this->connection->ping()) {
            $this->connection->close();
            $this->connect();
        }
        return $this->connection;
    }
    
    public function query($sql, $params = null) {
        $startTime = microtime(true);
        $this->queryCount++;
        
        if ($params) {
            $stmt = $this->connection->prepare($sql);
            if ($stmt) {
                $types = str_repeat('s', count($params));
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $result = $stmt->get_result();
                $stmt->close();
            } else {
                $result = false;
            }
        } else {
            $result = $this->connection->query($sql);
        }
        
        $queryTime = microtime(true) - $startTime;
        
        if ($queryTime > $this->slowQueryThreshold && 
            isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
            $this->logSlowQuery($sql, $queryTime);
        }
        
        return $result;
    }
    
    private function logSlowQuery($sql, $time) {
        $log = [
            'time' => date('Y-m-d H:i:s'),
            'query' => $sql,
            'duration' => round($time, 4) . 's'
        ];
        error_log("Slow Query: " . json_encode($log));
    }
    
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    public function getQueryCount() {
        return $this->queryCount;
    }
    
    public function getQueryLog() {
        return $this->queryLog;
    }
    
    public function beginTransaction() {
        return $this->connection->begin_transaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }
    
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    public function affectedRows() {
        return $this->connection->affected_rows;
    }
    
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    private function __clone() {}
    public function __wakeup() {}
}