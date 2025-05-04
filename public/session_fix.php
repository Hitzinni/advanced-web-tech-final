<?php
// Set proper PHP error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set cookie parameters first - must be before session_start()
session_set_cookie_params([
    'lifetime' => 86400, // 24 hours
    'path' => '/',
    'domain' => '',      // Current domain
    'secure' => false,   // Allow HTTP
    'httponly' => true,  // Prevent JavaScript access
    'samesite' => 'Lax'  // Allow cross-site requests
]);

// Start the session
session_start();

// Set content type
header('Content-Type: text/html; charset=UTF-8');

// Check if session is active
echo "<h1>Session Fix Utility</h1>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session Status: " . session_status() . " (3 means active)</p>";

// Initialize CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    echo "<p>New CSRF token generated: " . $_SESSION['csrf_token'] . "</p>";
} else {
    echo "<p>Existing CSRF token: " . $_SESSION['csrf_token'] . "</p>";
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        'items' => [],
        'total' => 0
    ];
    echo "<p>New cart initialized</p>";
} else {
    echo "<p>Cart already exists with " . count($_SESSION['cart']['items']) . " items</p>";
}

// Set a test session value
$_SESSION['test_value'] = "Session working at " . date('Y-m-d H:i:s');
echo "<p>Test value set: " . $_SESSION['test_value'] . "</p>";

// Create a test form that adds to cart
echo "<h2>Test Cart Form</h2>";
echo "<form action='api/cart/add.php' method='POST'>";
echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>";
echo "<div><label>Product ID: <input type='number' name='product_id' value='1'></label></div>";
echo "<div><label>Quantity: <input type='number' name='quantity' value='1'></label></div>";
echo "<button type='submit'>Add Test Item to Cart</button>";
echo "</form>";

// Add links to other pages
echo "<h2>Links</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Home</a></li>";
echo "<li><a href='cart'>View Cart</a></li>";
echo "<li><a href='products'>Browse Products</a></li>";
echo "<li><a href='cart_debug.php'>Debug Cart</a></li>";
echo "</ul>";

// Display cookie information
echo "<h2>Cookie Information</h2>";
echo "<pre>";
print_r($_COOKIE);
echo "</pre>";

// Done
echo "<p>Session fix complete. Try browsing the site now.</p>";
?> 