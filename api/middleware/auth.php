<?php

namespace App\Middleware;

class Auth {
    public static function requireAuth() {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Authentication required',
                'session' => session_id(),
                'cookies' => $_COOKIE
            ]);
            exit;
        }
        return $_SESSION['user'];
    }

    public static function requireRole($role) {
        $user = self::requireAuth();
        $roles = ['viewer' => 1, 'editor' => 2, 'admin' => 3];
        if (($roles[$user['role']] ?? 0) < ($roles[$role] ?? 0)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Insufficient permissions']);
            exit;
        }
        return $user;
    }

    public static function check() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['user']);
    }

    public static function getUser() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['user'] ?? null;
    }
}