<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHP Error Log Viewer</h1>";

// Try to get error log path
$errorLogPath = ini_get('error_log');
echo "<p>Error log path according to PHP configuration: <strong>" . htmlspecialchars($errorLogPath ?: 'Not set') . "</strong></p>";

// Common error log locations to check
$possibleLogLocations = [
    $errorLogPath,
    // Apache error logs
    '/var/log/apache2/error.log',
    '/var/log/httpd/error_log',
    // Windows paths
    'C:/xampp/apache/logs/error.log',
    // Current directory
    dirname(__FILE__) . '/error_log',
    dirname(__FILE__) . '/php_errors.log',
    dirname(dirname(__FILE__)) . '/error_log',
    dirname(dirname(__FILE__)) . '/logs/error_log',
    dirname(dirname(__FILE__)) . '/logs/php_errors.log',
    // PHP's error log in the temp directory
    sys_get_temp_dir() . '/php_errors.log',
];

// Find the first readable log file
$foundLog = false;
foreach ($possibleLogLocations as $logFile) {
    if (!empty($logFile) && file_exists($logFile) && is_readable($logFile)) {
        echo "<h2>Contents of error log: " . htmlspecialchars($logFile) . "</h2>";
        $logContent = file_get_contents($logFile);
        
        if ($logContent) {
            $foundLog = true;
            // Split into lines and display the last 100 lines
            $logLines = explode("\n", $logContent);
            $lastLines = array_slice($logLines, max(0, count($logLines) - 100));
            
            echo "<pre style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; overflow: auto; max-height: 500px;'>";
            foreach ($lastLines as $line) {
                // Highlight our own log messages for easier reading
                if (strpos($line, 'CartController') !== false) {
                    echo "<mark>" . htmlspecialchars($line) . "</mark>\n";
                } else {
                    echo htmlspecialchars($line) . "\n";
                }
            }
            echo "</pre>";
        } else {
            echo "<p>The log file exists but is empty or couldn't be read.</p>";
        }
    }
}

if (!$foundLog) {
    echo "<p>Could not find or read any error log files. Here are some server details that might help locate the logs:</p>";
    
    echo "<h3>Server Information</h3>";
    echo "<pre>";
    echo "PHP Version: " . phpversion() . "\n";
    echo "OS: " . PHP_OS . "\n";
    echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Not available') . "\n";
    echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not available') . "\n";
    echo "Script Filename: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'Not available') . "\n";
    echo "Temp Directory: " . sys_get_temp_dir() . "\n";
    echo "</pre>";
    
    // Try to create a test error to see where it goes
    echo "<h3>Attempting to generate a test error</h3>";
    error_log("TEST ERROR LOG ENTRY FROM show_errors.php - " . date('Y-m-d H:i:s'));
    echo "<p>A test error has been logged. Refresh this page in a few moments to see if any logs appear.</p>";
    
    // Print all PHP configuration settings to help debugging
    echo "<h3>PHP Configuration (error-related settings)</h3>";
    echo "<pre>";
    echo "display_errors: " . ini_get('display_errors') . "\n";
    echo "error_reporting: " . ini_get('error_reporting') . "\n";
    echo "log_errors: " . ini_get('log_errors') . "\n";
    echo "error_log: " . ini_get('error_log') . "\n";
    echo "display_startup_errors: " . ini_get('display_startup_errors') . "\n";
    echo "</pre>";
}

// Try to directly check if there are any errors in the current session
echo "<h3>Recent Errors Directly From Error Handler</h3>";
echo "<p>This might work if error_get_last() returns something useful.</p>";

$lastError = error_get_last();
if ($lastError) {
    echo "<pre>";
    print_r($lastError);
    echo "</pre>";
} else {
    echo "<p>No errors captured by error_get_last().</p>";
}
?> 