<?php
// Start the session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Initialize cart in session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        'items' => [],
        'total' => 0
    ];
}

// Calculate cart metrics
$cartItems = isset($_SESSION['cart']['items']) && is_array($_SESSION['cart']['items']) ? 
             $_SESSION['cart']['items'] : [];
$cartTotal = isset($_SESSION['cart']['total']) && is_numeric($_SESSION['cart']['total']) ? 
             $_SESSION['cart']['total'] : 0;

// Count total items (accounting for quantity)
$itemCount = 0;
foreach ($cartItems as $item) {
    $itemCount += isset($item['quantity']) ? (int)$item['quantity'] : 1;
}

// Ensure cart total is calculated correctly
if ($cartTotal == 0 && count($cartItems) > 0) {
    $recalculatedTotal = 0;
    foreach ($cartItems as $item) {
        $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
        $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
        $recalculatedTotal += $itemPrice * $itemQuantity;
    }
    $cartTotal = $recalculatedTotal;
    $_SESSION['cart']['total'] = $cartTotal;
}

// Create response
$response = [
    'success' => true,
    'itemCount' => $itemCount,
    'total' => (float)$cartTotal,
    'uniqueItems' => count($cartItems)
];

// Return JSON
echo json_encode($response);
exit; 