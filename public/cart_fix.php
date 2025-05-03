<?php
// Cart troubleshooting script - This is a standalone file to diagnose and fix cart rendering issues
// Enable all error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set BASE_PATH constant (required for templates)
define('BASE_PATH', dirname(__DIR__));

// Basic check of required components
echo "<h1>Cart Troubleshooting</h1>";

// Check PHP version
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// Check if autoloader exists
$autoloaderPath = BASE_PATH . '/vendor/autoload.php';
echo "<p><strong>Autoloader path:</strong> " . $autoloaderPath . "</p>";
echo "<p><strong>Autoloader exists:</strong> " . (file_exists($autoloaderPath) ? 'Yes' : 'No') . "</p>";

// Try to load autoloader
try {
    require_once $autoloaderPath;
    echo "<p style='color:green'><strong>✓ Autoloader loaded successfully</strong></p>";
} catch (Throwable $e) {
    echo "<p style='color:red'><strong>✗ Error loading autoloader:</strong> " . $e->getMessage() . "</p>";
}

// Basic environment checks completed, now try to set up a minimal cart
try {
    // Add a sample product to cart session for testing
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart']['items'])) {
        $_SESSION['cart'] = [
            'items' => [
                [
                    'id' => 1,
                    'name' => 'Test Product',
                    'price' => 19.99,
                    'quantity' => 1,
                    'category' => 'Test'
                ]
            ],
            'total' => 19.99
        ];
        echo "<p><strong>✓ Created test cart data</strong></p>";
    }
    
    // Check if template files exist
    $cartTemplate = BASE_PATH . '/src/views/cart.php';
    $layoutTemplate = BASE_PATH . '/src/views/layout.php';
    
    echo "<p><strong>Cart template path:</strong> " . $cartTemplate . "</p>";
    echo "<p><strong>Cart template exists:</strong> " . (file_exists($cartTemplate) ? 'Yes' : 'No') . "</p>";
    
    echo "<p><strong>Layout template path:</strong> " . $layoutTemplate . "</p>";
    echo "<p><strong>Layout template exists:</strong> " . (file_exists($layoutTemplate) ? 'Yes' : 'No') . "</p>";
    
    // Check if we can create a CartController
    if (class_exists('\\App\\Controllers\\CartController')) {
        echo "<p style='color:green'><strong>✓ CartController class found</strong></p>";
        
        // Try to create instance of CartController
        try {
            $controller = new \App\Controllers\CartController();
            echo "<p style='color:green'><strong>✓ CartController instance created</strong></p>";
            
            // Capture output
            ob_start();
            $controller->viewCart();
            $output = ob_get_clean();
            
            echo "<p><strong>CartController output size:</strong> " . strlen($output) . " bytes</p>";
            
            if (strlen($output) > 0) {
                echo "<hr>";
                echo "<h2>Cart Output Below:</h2>";
                echo $output;
            } else {
                echo "<p style='color:red'><strong>✗ No output from CartController</strong></p>";
            }
            
        } catch (Throwable $e) {
            echo "<p style='color:red'><strong>✗ Error creating CartController:</strong> " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
    } else {
        echo "<p style='color:red'><strong>✗ CartController class not found</strong></p>";
    }
    
} catch (Throwable $e) {
    echo "<p style='color:red'><strong>✗ Error during troubleshooting:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Provide links to alternative cart options
echo "<hr>";
echo "<h3>Try these alternative cart pages:</h3>";
echo "<ul>";
echo "<li><a href='cart_direct.php'>Direct cart page (standalone)</a></li>";
echo "<li><a href='index.php?route=cart&debug=true'>Cart page with debug enabled</a></li>";
echo "<li><a href='index.php?route=cart&no_template=true'>Cart page without template</a></li>";
echo "</ul>";
?> 