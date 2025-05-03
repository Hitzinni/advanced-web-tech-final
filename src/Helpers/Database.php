<?php
declare(strict_types=1);

namespace App\Helpers;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                // Teaching server specific settings
                // Extracting username from the path to determine database credentials
                $scriptPath = $_SERVER['SCRIPT_FILENAME'] ?? '';
                $username = '';
                
                // Check if we're on the teaching server
                if (strpos($scriptPath, '/prin/') !== false) {
                    // Extract username (x8m18 or similar) from path
                    if (preg_match('/\/prin\/([a-z0-9]+)\//', $scriptPath, $matches)) {
                        $username = $matches[1];
                    }
                }
                
                // Use extracted username for database credentials on teaching server
                // or fall back to environment variables/defaults
                if (!empty($username)) {
                    $host = 'localhost';
                    $name = $username;
                    $user = $username;
                    $pass = $username;
                } else {
                    // Default/local development settings
                    $host = $_ENV['DB_HOST'] ?? 'localhost';
                    $name = $_ENV['DB_NAME'] ?? 'grocery_store_dev';
                    $user = $_ENV['DB_USER'] ?? 'root';
                    $pass = $_ENV['DB_PASS'] ?? '';
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