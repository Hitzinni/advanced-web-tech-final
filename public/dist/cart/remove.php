<?php
// Start the session
session_start();

// Get the product ID from GET or POST
$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$productId) {
    $productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
}

// Validate product ID
if (!$productId) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'text' => 'Invalid product ID provided.'
    ];
    header('Location: ../cart');
    exit;
}

// Remove item from session cart
$removed = false;

if (isset($_SESSION['cart']) && isset($_SESSION['cart']['items']) && is_array($_SESSION['cart']['items'])) {
    $cartItems = $_SESSION['cart']['items'];
    $updatedItems = [];
    $cartTotal = 0;
    
    foreach ($cartItems as $item) {
        if (isset($item['id']) && $item['id'] == $productId) {
            $removed = true;
            continue; // Skip this item
        }
        
        $updatedItems[] = $item;
        
        // Recalculate total
        $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
        $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
        $cartTotal += $itemPrice * $itemQuantity;
    }
    
    // Update cart
    $_SESSION['cart']['items'] = $updatedItems;
    $_SESSION['cart']['total'] = $cartTotal;
}

// Set flash message
$_SESSION['flash_message'] = [
    'type' => $removed ? 'success' : 'danger',
    'text' => $removed ? 'Item removed successfully.' : 'Failed to remove item from cart.'
];

// Redirect back to cart page
header('Location: ../cart');
exit; 