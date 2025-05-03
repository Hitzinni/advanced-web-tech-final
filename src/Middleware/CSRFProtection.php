<?php
declare(strict_types=1);

namespace App\Middleware;

/**
 * CSRF Protection Middleware
 * Provides static methods for verifying and retrieving CSRF tokens stored in the session.
 */
class CSRFProtection
{
    /**
     * Verifies if a provided token matches the CSRF token stored in the session.
     * Uses hash_equals for timing-attack-safe comparison.
     * 
     * @param string|null $token The token submitted by the user (e.g., from a form).
     * @return bool True if the provided token is not null, the session token exists, and they match. False otherwise.
     */
    public static function verifyToken(?string $token): bool
    {
        // Retrieve the CSRF token stored in the session, default to empty string if not set.
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        // Check if the session token is not empty and if it matches the provided token.
        // hash_equals is crucial here for comparing strings in a way that prevents timing attacks.
        return !empty($sessionToken) && hash_equals($sessionToken, (string)$token);
    }
    
    /**
     * Retrieves the current CSRF token stored in the session.
     * Used typically to include the token in forms.
     *
     * @return string The CSRF token from the session, or an empty string if not set.
     */
    public static function getToken(): string
    {
        // Return the CSRF token from the session, defaulting to an empty string if it doesn't exist.
        return $_SESSION['csrf_token'] ?? '';
    }
} 