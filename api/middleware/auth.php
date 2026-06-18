<?php

namespace App\Middleware;

// This file is already included after bootstrap, so session is started.
class Auth {
    public static function requireAuth() {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Authentication required']);
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
        return isset($_SESSION['user']);
    }

    public static function getUser() {
        return $_SESSION['user'] ?? null;
    }
}