<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define BASE_PATH for autoloading
define('BASE_PATH', dirname(__DIR__));

// Start session
session_start();

try {
    // Load the autoloader
    require_once BASE_PATH . '/vendor/autoload.php';
    
    // Create cart controller instance and call viewCart method directly
    $controller = new \App\Controllers\CartController();
    $controller->viewCart();
    
} catch (Throwable $e) {
    // Display error
    echo '<div style="margin: 2rem; padding: 1rem; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 0.25rem;">';
    echo '<h1>Error Loading Cart Controller</h1>';
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
    echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
    echo '<p>Try using the <a href="cart_direct.php">direct cart page</a> instead.</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div>';
}
?> 