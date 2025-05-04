<?php
// Start the session
session_start();

// Set proper content type
header('Content-Type: text/html; charset=UTF-8');

// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to display arrays/objects in a readable format
function prettyPrint($data) {
    echo '<pre style="background-color:#f5f5f5; padding:15px; border-radius:5px; font-family:monospace;">';
    echo htmlspecialchars(print_r($data, true));
    echo '</pre>';
}

echo '<h1>Cart Debug Information</h1>';

// Debug Session information
echo '<h2>Session Data</h2>';
echo '<p>Session ID: ' . session_id() . '</p>';
echo '<p>Session Status: ' . session_status() . ' (1=disabled, 2=enabled but no session, 3=enabled and has session)</p>';

// Check if user is logged in
echo '<h2>User Information</h2>';
if (isset($_SESSION['user_id'])) {
    echo '<p style="color:green;">User is logged in with ID: ' . $_SESSION['user_id'] . '</p>';
    echo '<p>Email: ' . ($_SESSION['email'] ?? 'Not set') . '</p>';
} else {
    echo '<p style="color:red;">User is NOT logged in</p>';
}

// Check CSRF token
echo '<h2>CSRF Token</h2>';
if (isset($_SESSION['csrf_token'])) {
    echo '<p style="color:green;">CSRF Token exists: ' . $_SESSION['csrf_token'] . '</p>';
} else {
    echo '<p style="color:red;">CSRF Token does NOT exist</p>';
}

// Display cart data
echo '<h2>Cart Data</h2>';
if (isset($_SESSION['cart'])) {
    echo '<p style="color:green;">Cart exists in session</p>';
    
    echo '<h3>Cart Items (' . count($_SESSION['cart']['items']) . ')</h3>';
    
    if (empty($_SESSION['cart']['items'])) {
        echo '<p style="color:orange;">Cart is empty</p>';
    } else {
        echo '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">';
        echo '<tr style="background-color:#e0e0e0;">';
        echo '<th>ID</th><th>Name</th><th>Price</th><th>Quantity</th><th>Total</th><th>Category</th>';
        echo '</tr>';
        
        $totalItems = 0;
        foreach ($_SESSION['cart']['items'] as $item) {
            echo '<tr>';
            echo '<td>' . (isset($item['id']) ? $item['id'] : 'N/A') . '</td>';
            echo '<td>' . (isset($item['name']) ? htmlspecialchars($item['name']) : 'N/A') . '</td>';
            echo '<td>$' . (isset($item['price']) ? number_format($item['price'], 2) : '0.00') . '</td>';
            echo '<td>' . (isset($item['quantity']) ? $item['quantity'] : '0') . '</td>';
            echo '<td>$' . (isset($item['price']) && isset($item['quantity']) ? number_format($item['price'] * $item['quantity'], 2) : '0.00') . '</td>';
            echo '<td>' . (isset($item['category']) ? htmlspecialchars($item['category']) : 'N/A') . '</td>';
            echo '</tr>';
            
            $totalItems += isset($item['quantity']) ? (int)$item['quantity'] : 0;
        }
        
        echo '<tr style="background-color:#e0e0e0; font-weight:bold;">';
        echo '<td colspan="3">Total</td>';
        echo '<td>' . $totalItems . ' items</td>';
        echo '<td>$' . (isset($_SESSION['cart']['total']) ? number_format($_SESSION['cart']['total'], 2) : '0.00') . '</td>';
        echo '<td></td>';
        echo '</tr>';
        echo '</table>';
    }
    
    echo '<h3>Cart Calculated Total</h3>';
    echo '<p>$' . (isset($_SESSION['cart']['total']) ? number_format($_SESSION['cart']['total'], 2) : '0.00') . '</p>';
} else {
    echo '<p style="color:red;">Cart does NOT exist in session</p>';
}

// Display last flash message
echo '<h2>Last Flash Message</h2>';
if (isset($_SESSION['flash_message'])) {
    echo '<div style="padding:10px; border-radius:5px; border:1px solid #ccc;">';
    echo '<p>Type: ' . ($_SESSION['flash_message']['type'] ?? 'N/A') . '</p>';
    echo '<p>Text: ' . ($_SESSION['flash_message']['text'] ?? 'N/A') . '</p>';
    echo '</div>';
} else {
    echo '<p>No flash message in session</p>';
}

// Try to use the API endpoint directly to add a test product
echo '<h2>Test Add to Cart Functionality</h2>';
echo '<form action="api/cart/add.php" method="POST" style="padding:15px; border:1px solid #ccc; border-radius:5px;">';
echo '<input type="hidden" name="csrf_token" value="' . ($_SESSION['csrf_token'] ?? '') . '">';
echo '<div style="margin-bottom:10px;"><label>Product ID: <input type="number" name="product_id" value="1" required></label></div>';
echo '<div style="margin-bottom:10px;"><label>Quantity: <input type="number" name="quantity" value="1" min="1" required></label></div>';
echo '<button type="submit" style="padding:8px 16px; background-color:#0d6efd; color:white; border:none; border-radius:4px; cursor:pointer;">Add to Cart (Direct API Test)</button>';
echo '</form>';

// Links to other relevant pages
echo '<h2>Useful Links</h2>';
echo '<ul>';
echo '<li><a href="index.php">Home Page</a></li>';
echo '<li><a href="products">Products Page</a></li>';
echo '<li><a href="cart">View Cart</a></li>';
echo '<li><a href="api/cart/info.php">View Cart Info API</a></li>';
echo '<li><a href="logout.php">Logout</a></li>';
echo '</ul>'; 