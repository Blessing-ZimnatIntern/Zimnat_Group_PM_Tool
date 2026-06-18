<?php

namespace App\Config;

use Dotenv\Dotenv;

class Environment {
    private static $loaded = false;
    
    public static function load() {
        if (self::$loaded) {
            return;
        }
        
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
        
        // Validate required variables
        self::validateRequired();
        
        self::$loaded = true;
    }
    
    private static function validateRequired() {
        $required = [
            'DB_HOST',
            'DB_DATABASE',
            'DB_USERNAME'
        ];
        
        $missing = [];
        foreach ($required as $key) {
            if (!isset($_ENV[$key]) || empty($_ENV[$key])) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            die(json_encode([
                'error' => 'Missing required environment variables',
                'missing' => $missing
            ]));
        }
    }
    
    public static function get($key, $default = null) {
        self::load();
        return $_ENV[$key] ?? $default;
    }
    
    public static function isDevelopment() {
        return self::get('APP_ENV') === 'development';
    }
    
    public static function isProduction() {
        return self::get('APP_ENV') === 'production';
    }
    
    public static function isDebug() {
        return self::get('APP_DEBUG', 'false') === 'true';
    }
}