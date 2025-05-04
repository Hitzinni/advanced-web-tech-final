<?php
/**
 * Direct configuration file to replace .env
 * This is needed because parse_ini_file() is disabled on the server
 */

// Teaching server auto-detection
$scriptPath = $_SERVER['SCRIPT_FILENAME'] ?? '';
$username = 'x8m18';

// Check if we're on the teaching server
if (strpos($scriptPath, '/prin/') !== false) {
    // Extract username (x8m18 or similar) from path
    if (preg_match('/\/prin\/([a-z0-9]+)\//', $scriptPath, $matches)) {
        $username = $matches[1];
        error_log("Extracted username from path: $username");
    } else {
        error_log("Failed to extract username from path: $scriptPath");
        // Hardcode the username as a fallback for this specific case
        $username = 'x8m18';
        error_log("Using hardcoded fallback username: $username");
    }
}

// Configure database settings based on environment
if (!empty($username)) {
    // Teaching server settings
    define('DB_HOST', 'localhost');
    define('DB_NAME', $username);
    define('DB_USER', $username);
    define('DB_PASS', $username . $username); // Username twice for teaching server password
    error_log("Using teaching server credentials - User: $username, DB: $username");
} else {
    // Local development settings
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'grocery_store_dev');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// Always define the charset
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_URL', '');
define('APP_ENV', 'development');
define('APP_DEBUG', true);

// JWT configuration (if needed)
define('JWT_SECRET', 'your-secret-key');
define('JWT_EXPIRY', 3600); // 1 hour

// Email configuration (if needed)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'user@example.com');
define('SMTP_PASS', '');

// Custom configuration for server path
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__FILE__));
}
?> 