<?php
// Show all PHP errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Basic HTML structure
echo '<!DOCTYPE html>
<html>
<head>
    <title>PHP Diagnostic Info</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>PHP Diagnostic Information</h1>';

// PHP Version
echo '<div class="section">
    <h2>PHP Version</h2>
    <pre>' . phpversion() . '</pre>
</div>';

// Server Information
echo '<div class="section">
    <h2>Server Information</h2>
    <pre>' . $_SERVER['SERVER_SOFTWARE'] . '</pre>
</div>';

// Error Log Location
echo '<div class="section">
    <h2>Error Log Location</h2>
    <pre>' . ini_get('error_log') . '</pre>
</div>';

// PHP Extensions
echo '<div class="section">
    <h2>Loaded PHP Extensions</h2>
    <pre>' . implode(', ', get_loaded_extensions()) . '</pre>
</div>';

// PDO Drivers Available
echo '<div class="section">
    <h2>Available PDO Drivers</h2>
    <pre>' . implode(', ', PDO::getAvailableDrivers()) . '</pre>
</div>';

// Memory Limits
echo '<div class="section">
    <h2>PHP Memory Settings</h2>
    <table>
        <tr><th>Setting</th><th>Value</th></tr>
        <tr><td>memory_limit</td><td>' . ini_get('memory_limit') . '</td></tr>
        <tr><td>max_execution_time</td><td>' . ini_get('max_execution_time') . '</td></tr>
        <tr><td>post_max_size</td><td>' . ini_get('post_max_size') . '</td></tr>
        <tr><td>upload_max_filesize</td><td>' . ini_get('upload_max_filesize') . '</td></tr>
    </table>
</div>';

// Recent errors from error log (if accessible)
echo '<div class="section">
    <h2>Recent Error Log Entries</h2>';
$errorLogFile = ini_get('error_log');
if (file_exists($errorLogFile) && is_readable($errorLogFile)) {
    $errorLog = file_get_contents($errorLogFile);
    // Get last few KB of the log
    $logSize = strlen($errorLog);
    $errorLog = substr($errorLog, max(0, $logSize - 10000), 10000);
    echo '<pre>' . htmlspecialchars($errorLog) . '</pre>';
} else {
    echo '<p>Error log not accessible or empty.</p>';
}
echo '</div>';

// Database Test Connection
echo '<div class="section">
    <h2>Database Connection Test</h2>';
try {
    // Include the config file directly instead of using parse_ini_file
    require_once '../config.php';
    
    $host = DB_HOST;
    $dbname = DB_NAME;
    $user = DB_USER;
    $pass = DB_PASS;
    
    echo "<p>Attempting to connect to MySQL database: $dbname on $host</p>";
    
    if (extension_loaded('pdo_mysql')) {
        $dsn = "mysql:host=$host;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo '<p style="color:green">Connection successful!</p>';
        
        // Check for tables
        echo '<h3>Database Tables</h3>';
        $result = $pdo->query('SHOW TABLES');
        echo '<table>';
        echo '<tr><th>Table Name</th></tr>';
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            echo '<tr><td>' . htmlspecialchars($row[0]) . '</td></tr>';
        }
        echo '</table>';
    } else {
        echo '<p style="color:red">PDO MySQL extension not loaded.</p>';
    }
} catch (Exception $e) {
    echo '<p style="color:red">Database connection error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
echo '</div>';

echo '</body></html>';
?> 