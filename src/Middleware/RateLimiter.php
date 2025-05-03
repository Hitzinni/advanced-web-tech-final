<?php
declare(strict_types=1);

namespace App\Middleware;

/**
 * Rate Limiter Middleware
 * Provides simple rate limiting functionality based on session data.
 */
class RateLimiter
{
    /**
     * Throttles requests based on a key, maximum attempts, and decay time.
     * Stores attempt timestamps in the session.
     * If the maximum attempts are exceeded within the decay period, it sends a 429 response and exits.
     * 
     * @param string $key A unique key identifying the action being throttled (e.g., 'login:192.168.1.1').
     * @param int $maxAttempts The maximum number of allowed attempts within the decay period.
     * @param int $decaySeconds The duration (in seconds) to consider attempts for rate limiting.
     * @return void
     */
    public static function throttle(string $key, int $maxAttempts, int $decaySeconds): void
    {
        // Ensure session is started to access session data
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Retrieve the array of attempt timestamps for this key from the session, or initialize an empty array
        $attempts = $_SESSION['rate_limits'][$key] ?? [];
        
        // --- Attempt Cleanup ---
        // Filter out attempts older than 1 hour (3600 seconds) to prevent session bloat.
        // This cleanup window is independent of the $decaySeconds for throttling.
        $attempts = array_filter($attempts, function($timestamp) {
            return $timestamp > time() - 3600; // Keep only attempts from the last hour
        });
        
        // --- Throttling Check ---
        // Filter the remaining attempts to find those within the specified decay window.
        $recentAttempts = array_filter($attempts, function($timestamp) use ($decaySeconds) {
            return $timestamp > time() - $decaySeconds; // Keep attempts within the decay period
        });
        
        // Check if the count of recent attempts meets or exceeds the maximum allowed attempts.
        if (count($recentAttempts) >= $maxAttempts) {
            // If rate limit is exceeded, send a 429 Too Many Requests header.
            header('HTTP/1.1 429 Too Many Requests');
            // Output a user-friendly message.
            echo 'Too many attempts. Please try again later.';
            // Terminate script execution.
            exit;
        }
        
        // --- Record Current Attempt ---
        // If the rate limit is not exceeded, record the timestamp of the current attempt.
        $attempts[] = time();
        // Store the updated list of attempts back into the session for this key.
        $_SESSION['rate_limits'][$key] = $attempts;
    }
} 