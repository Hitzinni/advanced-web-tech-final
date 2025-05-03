<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define BASE_PATH for autoloading
define('BASE_PATH', dirname(__DIR__));

// Autoload classes
require_once BASE_PATH . '/vendor/autoload.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Get database instance
    $db = \App\Helpers\Database::getInstance();
    
    // Test query
    $stmt = $db->query("SELECT 'Connection successful!' as message");
    $result = $stmt->fetch();
    
    echo "<div style='color: green; font-weight: bold;'>";
    echo "✓ " . htmlspecialchars($result['message']);
    echo "</div>";
    
    // Display server path info
    echo "<h2>Server Path Information</h2>";
    echo "<pre>";
    echo "SCRIPT_FILENAME: " . htmlspecialchars($_SERVER['SCRIPT_FILENAME'] ?? 'Not set') . "\n";
    echo "DOCUMENT_ROOT: " . htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "\n";
    echo "PHP_SELF: " . htmlspecialchars($_SERVER['PHP_SELF'] ?? 'Not set') . "\n";
    echo "REQUEST_URI: " . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'Not set') . "\n";
    echo "</pre>";
    
    // Check if php.ini settings might be affecting the connection
    echo "<h2>PHP Configuration</h2>";
    echo "<pre>";
    echo "PDO MySQL Extension: " . (extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled') . "\n";
    echo "MySQL Extension: " . (extension_loaded('mysql') ? 'Enabled' : 'Disabled') . "\n";
    echo "allow_url_fopen: " . ini_get('allow_url_fopen') . "\n";
    echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<div style='color: red; font-weight: bold;'>";
    echo "✗ Database Connection Error: " . htmlspecialchars($e->getMessage());
    echo "</div>";
    
    // Display additional debug info
    echo "<h2>Debug Information</h2>";
    echo "<pre>";
    // Get the Database class to inspect
    $reflector = new ReflectionClass('\App\Helpers\Database');
    $file = $reflector->getFileName();
    echo "Database.php location: " . htmlspecialchars($file) . "\n\n";
    
    // Display server path info
    echo "SCRIPT_FILENAME: " . htmlspecialchars($_SERVER['SCRIPT_FILENAME'] ?? 'Not set') . "\n";
    echo "DOCUMENT_ROOT: " . htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "\n";
    echo "PHP_SELF: " . htmlspecialchars($_SERVER['PHP_SELF'] ?? 'Not set') . "\n";
    echo "REQUEST_URI: " . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'Not set') . "\n";
    echo "</pre>";
    
    // Check error log
    echo "<h2>Recent Error Log Entries</h2>";
    echo "<pre>";
    $errorLog = ini_get('error_log');
    echo "Error log path: " . htmlspecialchars($errorLog) . "\n\n";
    
    // Try to read the error log if possible
    if (file_exists($errorLog) && is_readable($errorLog)) {
        $logContent = file_get_contents($errorLog);
        $logLines = explode("\n", $logContent);
        $lastLines = array_slice($logLines, -20); // Get last 20 lines
        echo htmlspecialchars(implode("\n", $lastLines));
    } else {
        echo "Cannot read error log file";
    }
    echo "</pre>";
}
?> 