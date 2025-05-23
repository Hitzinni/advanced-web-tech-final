<?php
declare(strict_types=1);

namespace App\Helpers;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                // Load config file if not already included
                if (!defined('DB_HOST')) {
                    require_once dirname(dirname(__DIR__)) . '/config.php';
                }
                
                // Teaching server specific settings
                // Extracting username from the path to determine database credentials
                $scriptPath = $_SERVER['SCRIPT_FILENAME'] ?? '';
                $username = '';
                
                // Check if we're on the teaching server
                if (strpos($scriptPath, '/prin/') !== false) {
                    // Extract username (x8m18 or similar) from path
                    // Updated regex to handle paths with spaces
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
                
                // Log the script path for debugging
                error_log("Script path: $scriptPath");
                
                // Use extracted username for database credentials on teaching server
                // or fall back to config.php values
                if (!empty($username)) {
                    $host = 'localhost';
                    $name = $username;
                    $user = $username;
                    $pass = $username . $username; // Username twice for teaching server password
                    error_log("Using teaching server credentials - User: $user, DB: $name, Pass: [hidden]");
                } else {
                    // Default/local development settings from config.php
                    $host = defined('DB_HOST') ? DB_HOST : 'localhost';
                    $name = defined('DB_NAME') ? DB_NAME : 'grocery_store_dev';
                    $user = defined('DB_USER') ? DB_USER : 'root';
                    $pass = defined('DB_PASS') ? DB_PASS : '';
                    error_log("Using default credentials - Host: $host, DB: $name, User: $user");
                }
                
                // Log connection attempt
                error_log("Database connection attempt - Host: {$host}, DB: {$name}, User: {$user}");
                
                $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                // Create PDO instance
                self::$instance = new PDO($dsn, $user, $pass, $options);
                
                // Test connection with a simple query
                $testQuery = self::$instance->query("SELECT 1");
                if ($testQuery) {
                    error_log("Database connection successful");
                } else {
                    error_log("Database connection created but test query failed");
                }
            } catch (PDOException $e) {
                // Log detailed error
                error_log("Database connection failed: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                
                // Display a more user-friendly error message with debug info if requested
                if (isset($_GET['debug']) && $_GET['debug'] === 'db') {
                    echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; font-family: sans-serif;">';
                    echo '<h1>Database Connection Error</h1>';
                    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '<h3>Connection Details:</h3>';
                    echo '<ul>';
                    echo '<li>Host: ' . htmlspecialchars($host) . '</li>';
                    echo '<li>Database: ' . htmlspecialchars($name) . '</li>';
                    echo '<li>Username: ' . htmlspecialchars($user) . '</li>';
                    echo '</ul>';
                    echo '<p>Add ?debug=exceptions to the URL to see full exception details.</p>';
                    echo '</div>';
                }
                
                // In production, log error and display friendly message
                throw new PDOException("Database connection failed: " . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
} 