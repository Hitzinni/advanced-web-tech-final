<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Load the cart page through the main index.php but with debug output
echo "Attempting to load cart page...<br>";
echo "Current session cart data: <pre>" . print_r($_SESSION['cart'] ?? 'No cart data', true) . "</pre><br>";

try {
    // Save output buffer
    ob_start();
    
    // Set the route to cart
    $_GET['route'] = 'cart';
    
    // Include the index.php to process the request
    include 'index.php';
    
    // Get the output
    $output = ob_get_clean();
    
    // Display the output
    echo $output;
} catch (Throwable $e) {
    // If there was an error
    ob_end_clean();
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?> 