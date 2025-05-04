<?php
// Enhanced debugging file for my-orders
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include the config file
require_once BASE_PATH . '/config.php';

// Try to load the autoloader if it exists
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
    echo "<h3>Error Detected:</h3>";
    echo "<p><strong>Error:</strong> [$errno] $errstr</p>";
    echo "<p><strong>File:</strong> $errfile (Line $errline)</p>";
    echo "</div>";
    return true;
});

// Custom exception handler
set_exception_handler(function($exception) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
    echo "<h3>Uncaught Exception:</h3>";
    echo "<p><strong>Message:</strong> " . $exception->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $exception->getFile() . " (Line " . $exception->getLine() . ")</p>";
    echo "<p><strong>Trace:</strong></p>";
    echo "<pre>" . $exception->getTraceAsString() . "</pre>";
    echo "</div>";
});

// Function to debug variables
function debug_var($var, $name = null) {
    echo "<div style='background: #f0f8ff; border: 1px solid #cce5ff; color: #004085; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
    if ($name) {
        echo "<h3>$name:</h3>";
    }
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
    echo "</div>";
}

// Display server information
echo "<h1>My Orders Diagnostic Page</h1>";
echo "<p>This page will attempt to execute the my-orders route and show any errors.</p>";

// Display important server variables
debug_var($_SERVER['REQUEST_URI'], 'REQUEST_URI');
debug_var($_SERVER['SCRIPT_NAME'], 'SCRIPT_NAME');
debug_var(getcwd(), 'Current Working Directory');
debug_var(BASE_PATH, 'BASE_PATH Constant');

// Check if the OrderController exists
$controllerPath = BASE_PATH . '/src/Controllers/OrderController.php';
if (file_exists($controllerPath)) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
    echo "<p>✅ OrderController.php exists at $controllerPath</p>";
    echo "</div>";
    
    // Try to require the file
    require_once $controllerPath;
    
    // Check if the class exists and is loaded
    if (class_exists('\App\Controllers\OrderController')) {
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
        echo "<p>✅ OrderController class exists</p>";
        echo "</div>";
        
        // Try to create the controller
        try {
            $controller = new \App\Controllers\OrderController();
            echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
            echo "<p>✅ OrderController instance created</p>";
            echo "</div>";
            
            // Try to call the myOrders method
            echo "<h2>Attempting to call myOrders method:</h2>";
            $controller->myOrders();
        } catch (\Throwable $e) {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
            echo "<h3>Error Creating Controller:</h3>";
            echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
            echo "<p><strong>File:</strong> " . $e->getFile() . " (Line " . $e->getLine() . ")</p>";
            echo "<p><strong>Trace:</strong></p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
        echo "<p>❌ OrderController class does not exist</p>";
        echo "</div>";
    }
} else {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
    echo "<p>❌ OrderController.php not found at $controllerPath</p>";
    echo "</div>";
}

// Display session data if available
if (!empty($_SESSION)) {
    debug_var($_SESSION, 'Session Data');
} else {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 15px; margin-bottom: 20px; border-radius: 5px;'>";
    echo "<p>⚠️ Session is empty</p>";
    echo "</div>";
}

// Link to go back to the working orders page
echo "<div style='margin-top: 30px;'>";
echo "<a href='direct-orders.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Go to Working Orders Page</a>";
echo "</div>";
?> 