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
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $name = $_ENV['DB_NAME'] ?? 'grocery_store_dev';
                $user = $_ENV['DB_USER'] ?? 'root';
                $pass = $_ENV['DB_PASS'] ?? '';
                
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
                error_log("DSN: mysql:host={$host};dbname={$name};charset=utf8mb4");
                error_log("Stack trace: " . $e->getTraceAsString());
                
                // In production, log error and display friendly message
                throw new PDOException("Database connection failed: " . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
} 