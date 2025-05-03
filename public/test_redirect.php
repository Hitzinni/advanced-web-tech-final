<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Redirect Test</h1>";
echo "<p>This page will attempt to redirect to cart_direct.php in 5 seconds...</p>";
echo "<p>Current time: " . date('H:i:s') . "</p>";
echo "<hr>";

// Log the attempt
error_log("test_redirect.php: Attempting to redirect at " . date('Y-m-d H:i:s'));

// Output the code we're about to execute
echo "<h3>Redirection code to be executed:</h3>";
echo "<pre>";
echo htmlspecialchars('
$cartDirectUrl = str_replace("index.php", "cart_direct.php", $_SERVER["SCRIPT_NAME"]);
echo "Redirecting to: " . $cartDirectUrl;
header("Location: " . $cartDirectUrl);
exit;
');
echo "</pre>";

// Display server info
echo "<h3>Server variables:</h3>";
echo "<pre>";
echo "SCRIPT_NAME: " . htmlspecialchars($_SERVER['SCRIPT_NAME']) . "\n";
echo "Calculated redirect URL: " . htmlspecialchars(str_replace("test_redirect.php", "cart_direct.php", $_SERVER['SCRIPT_NAME'])) . "\n";
echo "</pre>";

// Add a manual link as fallback
echo "<p>If automatic redirect doesn't work, <a href='cart_direct.php'>click here</a>.</p>";

// Force output before redirect
flush();

// Wait 5 seconds to allow reading the page
sleep(5);

// Now attempt the redirect
try {
    $cartDirectUrl = str_replace("test_redirect.php", "cart_direct.php", $_SERVER['SCRIPT_NAME']);
    echo "<p>Redirecting to: " . htmlspecialchars($cartDirectUrl) . "</p>";
    
    // Explicitly turn off output buffering to ensure all content is displayed
    while (ob_get_level()) {
        ob_end_flush();
    }
    flush();
    
    // Log the redirection
    error_log("test_redirect.php: Executing header redirect to " . $cartDirectUrl);
    
    // Perform the redirect
    header("Location: " . $cartDirectUrl);
    exit("Redirect should have happened. If you're seeing this, it failed.");
} catch (Exception $e) {
    echo "<p>Error during redirect: " . htmlspecialchars($e->getMessage()) . "</p>";
    error_log("test_redirect.php: Redirect error - " . $e->getMessage());
}
?> 