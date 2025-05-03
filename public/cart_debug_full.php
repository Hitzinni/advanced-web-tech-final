<?php
// Comprehensive cart debugging script
// This script traces each step of the cart loading process

// Enable output buffering to prevent "headers already sent" errors
ob_start();

// Show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define base path
define('BASE_PATH', dirname(__DIR__));
echo "<p><strong>BASE_PATH:</strong> " . BASE_PATH . "</p>";

// Start timer for performance analysis  
$startTime = microtime(true);

// Start session
session_start();
echo "<p><strong>Session started</strong>. Session ID: " . session_id() . "</p>";

// Check if the autoloader exists and can be loaded
$autoloaderPath = BASE_PATH . '/vendor/autoload.php';
echo "<p><strong>Autoloader path:</strong> " . $autoloaderPath . "</p>";
echo "<p><strong>Autoloader exists:</strong> " . (file_exists($autoloaderPath) ? 'Yes' : 'No') . "</p>";

try {
    // Load the autoloader
    require_once $autoloaderPath;
    echo "<p><strong>Autoloader loaded successfully</strong></p>";
} catch (Throwable $e) {
    echo "<p style='color: red'><strong>Error loading autoloader:</strong> " . $e->getMessage() . "</p>";
    exit("Cannot proceed without autoloader");
}

// Check if key classes exist
$requiredClasses = [
    '\\App\\Controllers\\CartController',
    '\\App\\Models\\CartModel',
    '\\App\\Helpers\\View',
    '\\App\\Helpers\\Database'
];

echo "<h3>Checking required classes:</h3><ul>";
foreach ($requiredClasses as $class) {
    echo "<li><strong>" . $class . ":</strong> " . (class_exists($class) ? 'Exists' : 'Missing') . "</li>";
}
echo "</ul>";

// Check View helper methods
try {
    echo "<h3>Testing View helper methods:</h3>";
    echo "<p><strong>View::getBaseUrl():</strong> " . \App\Helpers\View::getBaseUrl() . "</p>";
    echo "<p><strong>View::url('cart'):</strong> " . \App\Helpers\View::url('cart') . "</p>";
    
    // Test if template files exist
    $templatePath = BASE_PATH . '/src/views/cart.php';
    $layoutPath = BASE_PATH . '/src/views/layout.php';
    
    echo "<p><strong>Cart template exists:</strong> " . (file_exists($templatePath) ? 'Yes' : 'No') . "</p>";
    if (file_exists($templatePath)) {
        echo "<p><strong>Cart template permissions:</strong> " . substr(sprintf('%o', fileperms($templatePath)), -4) . "</p>";
        echo "<p><strong>Cart template size:</strong> " . filesize($templatePath) . " bytes</p>";
    }
    
    echo "<p><strong>Layout template exists:</strong> " . (file_exists($layoutPath) ? 'Yes' : 'No') . "</p>";
    if (file_exists($layoutPath)) {
        echo "<p><strong>Layout template permissions:</strong> " . substr(sprintf('%o', fileperms($layoutPath)), -4) . "</p>";
    }
} catch (Throwable $e) {
    echo "<p style='color: red'><strong>Error testing View helper:</strong> " . $e->getMessage() . "</p>";
}

// Test database connection
try {
    echo "<h3>Testing database connection:</h3>";
    $db = \App\Helpers\Database::getInstance();
    echo "<p style='color: green'><strong>Database connection successful</strong></p>";
    
    // Try a simple query
    $query = $db->query("SELECT 1 AS test");
    $result = $query->fetch();
    echo "<p><strong>Test query result:</strong> " . ($result['test'] ?? 'No result') . "</p>";
} catch (Throwable $e) {
    echo "<p style='color: red'><strong>Database connection error:</strong> " . $e->getMessage() . "</p>";
}

// Check session cart data
echo "<h3>Current session cart data:</h3>";
if (isset($_SESSION['cart'])) {
    echo "<pre>" . htmlspecialchars(print_r($_SESSION['cart'], true)) . "</pre>";
} else {
    echo "<p>No cart data in session</p>";
}

// Now attempt to create a CartController and call viewCart()
echo "<h3>Testing CartController:</h3>";
try {
    // Create controller instance
    $controller = new \App\Controllers\CartController();
    echo "<p><strong>CartController created successfully</strong></p>";
    
    // Capture output from viewCart method
    ob_start();
    $controller->viewCart();
    $cartOutput = ob_get_clean();
    
    // Show the size of the output
    echo "<p><strong>viewCart() output size:</strong> " . strlen($cartOutput) . " bytes</p>";
    
    // Show a preview of the output (first 500 characters)
    if (strlen($cartOutput) > 0) {
        echo "<p><strong>viewCart() output preview:</strong></p>";
        echo "<pre>" . htmlspecialchars(substr($cartOutput, 0, 500)) . 
             (strlen($cartOutput) > 500 ? '...' : '') . "</pre>";
    } else {
        echo "<p style='color: red'><strong>viewCart() produced no output</strong></p>";
    }
} catch (Throwable $e) {
    echo "<p style='color: red'><strong>Error testing CartController:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Trace:</strong></p><pre>" . $e->getTraceAsString() . "</pre>";
}

// Performance summary
$endTime = microtime(true);
$executionTime = ($endTime - $startTime) * 1000;
echo "<h3>Performance:</h3>";
echo "<p><strong>Execution time:</strong> " . number_format($executionTime, 2) . " ms</p>";
echo "<p><strong>Memory usage:</strong> " . number_format(memory_get_usage() / 1024 / 1024, 2) . " MB</p>";
echo "<p><strong>Peak memory usage:</strong> " . number_format(memory_get_peak_usage() / 1024 / 1024, 2) . " MB</p>";

// Check error logs
echo "<h3>Recent error log entries:</h3>";
$logPath = BASE_PATH . '/logs/php_errors.log';
if (file_exists($logPath) && is_readable($logPath)) {
    $logContent = file_get_contents($logPath);
    $lines = explode("\n", $logContent);
    $lastLines = array_slice($lines, -20); // Get last 20 lines
    
    echo "<pre>";
    foreach ($lastLines as $line) {
        if (strpos($line, 'cart') !== false || strpos($line, 'Cart') !== false) {
            echo "<mark>" . htmlspecialchars($line) . "</mark>\n";
        } else {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>Error log not found or not readable at: $logPath</p>";
}

// Add a section to test rendering the cart template directly
echo "<h3>Testing direct template rendering:</h3>";
try {
    // Create sample cart data
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [
            'items' => [
                [
                    'id' => 1,
                    'name' => 'Sample Product',
                    'price' => 19.99,
                    'quantity' => 1,
                    'category' => 'Test'
                ]
            ],
            'total' => 19.99
        ];
    }
    
    $cartItems = $_SESSION['cart']['items'];
    $cartTotal = $_SESSION['cart']['total'];
    
    // Try to include the template file directly
    echo "<p><strong>Attempting to render cart template directly...</strong></p>";
    
    // Buffer the output to prevent it affecting our debug page
    ob_start();
    include $templatePath;
    $templateOutput = ob_get_clean();
    
    echo "<p><strong>Template output size:</strong> " . strlen($templateOutput) . " bytes</p>";
    echo "<p><strong>Template output preview:</strong></p>";
    echo "<pre>" . htmlspecialchars(substr($templateOutput, 0, 500)) . 
         (strlen($templateOutput) > 500 ? '...' : '') . "</pre>";
    
} catch (Throwable $e) {
    echo "<p style='color: red'><strong>Error rendering template directly:</strong> " . $e->getMessage() . "</p>";
}

// See the full content of the cart template for analysis
echo "<h3>Cart template content:</h3>";
if (file_exists($templatePath)) {
    $templateContent = file_get_contents($templatePath);
    
    // Check for BOM issues
    $hasBOM = substr($templateContent, 0, 3) === "\xEF\xBB\xBF";
    echo "<p><strong>Has BOM (Byte Order Mark):</strong> " . ($hasBOM ? 'Yes' : 'No') . "</p>";
    
    // Check for encoding issues
    $isUTF8 = mb_check_encoding($templateContent, 'UTF-8');
    echo "<p><strong>Valid UTF-8:</strong> " . ($isUTF8 ? 'Yes' : 'No') . "</p>";
    
    // Output the file content with line numbers
    echo "<pre style='max-height: 400px; overflow-y: auto; background-color: #f8f9fa; padding: 10px; border: 1px solid #ddd;'>";
    $lines = explode("\n", $templateContent);
    foreach ($lines as $lineNumber => $line) {
        $lineNumber++;
        echo sprintf("%03d: %s\n", $lineNumber, htmlspecialchars($line));
    }
    echo "</pre>";
} else {
    echo "<p>Cart template file not found</p>";
}

// Add a button to retry the cart page with different parameters
echo "<h3>Try alternative approaches:</h3>";
echo "<a href='index.php?route=cart&debug=true' class='button' style='display: inline-block; padding: 10px 15px; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Try Cart with Debug</a>";
echo "<a href='index.php?route=cart&no_template=true' class='button' style='display: inline-block; padding: 10px 15px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Try Cart without Template</a>";
echo "<a href='cart_direct.php' class='button' style='display: inline-block; padding: 10px 15px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 4px;'>Go to Direct Cart</a>";
?> 