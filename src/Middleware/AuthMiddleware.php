<?php
declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware
{
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    public static function requireAuth(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }
    }
    
    public static function getUserId()
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }
    
    public static function isManager(): bool
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'manager';
    }
    
    public static function requireManager(): void
    {
        if (!self::isManager()) {
            header('HTTP/1.1 403 Forbidden');
            echo 'Access denied';
            exit;
        }
    }
} 