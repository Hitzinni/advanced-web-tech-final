<?php
// Start the session
session_start();

// Initialize cart in session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        'items' => [],
        'total' => 0
    ];
}

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

// Redirect back to cart page
header('Location: ../cart');
exit; 