<?php
// Start the session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Clear the cart in session
$_SESSION['cart'] = [
    'items' => [],
    'total' => 0
];

// Set success message
$_SESSION['flash_message'] = [
    'type' => 'success',
    'text' => 'Your cart has been cleared.'
];

// Return JSON response if it's an AJAX request
if ($isAjax) {
    echo json_encode([
        'success' => true,
        'message' => 'Cart cleared successfully',
        'itemCount' => 0,
        'total' => 0
    ]);
} else {
    // Redirect back to cart page
    header('Location: /cart');
}
exit; 