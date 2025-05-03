<?php
// Script to check and clear PHP caches

// Output information
echo "<h1>PHP Cache Information</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

// Try to clear OPcache if it exists
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p>OPcache has been reset.</p>";
} else {
    echo "<p>OPcache is not available.</p>";
}

// Check if APCu is installed
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "<p>APCu cache has been cleared.</p>";
} else {
    echo "<p>APCu is not available.</p>";
}

// Print out PHP info
echo "<h2>PHP Environment Info:</h2>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
echo "<li>Current Script: " . $_SERVER['SCRIPT_FILENAME'] . "</li>";
echo "</ul>";

// Check the cart controller file
$cartControllerPath = __DIR__ . '/src/controllers/CartController.php';
echo "<h2>Cart Controller File Info:</h2>";
if (file_exists($cartControllerPath)) {
    echo "<p>File exists at: $cartControllerPath</p>";
    echo "<p>File size: " . filesize($cartControllerPath) . " bytes</p>";
    echo "<p>Last modified: " . date('Y-m-d H:i:s', filemtime($cartControllerPath)) . "</p>";
    
    // Check for our specific function
    $content = file_get_contents($cartControllerPath);
    if (strpos($content, 'applyMeatFruitsVegetablesPromotion') !== false) {
        echo "<p style='color:green'>The promotion function was found in the controller!</p>";
    } else {
        echo "<p style='color:red'>The promotion function was NOT found in the controller!</p>";
    }
} else {
    echo "<p>File does not exist at: $cartControllerPath</p>";
}

// Display other relevant files
echo "<h2>Cart-related Files:</h2>";
$files = [
    'src/views/cart.php',
    'src/models/CartModel.php',
    'src/Helpers/PromotionHelper.php'
];

echo "<ul>";
foreach ($files as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "<li>$file exists, last modified: " . date('Y-m-d H:i:s', filemtime($fullPath)) . "</li>";
    } else {
        echo "<li>$file does not exist</li>";
    }
}
echo "</ul>";

echo "<p>Done.</p>";
?> 