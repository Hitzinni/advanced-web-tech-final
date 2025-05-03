<?php
// Start the session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Get the product ID from GET or POST
$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$productId) {
    $productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
}

// Validate product ID
if (!$productId) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'text' => 'Invalid product ID provided.'
        ];
        header('Location: /cart');
        exit;
    }
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

// Calculate total quantity for response
$itemCount = 0;
foreach ($_SESSION['cart']['items'] as $item) {
    $itemCount += isset($item['quantity']) ? (int)$item['quantity'] : 1;
}

// Return JSON response if it's an AJAX request
if ($isAjax) {
    echo json_encode([
        'success' => $removed,
        'message' => $removed ? 'Item removed successfully' : 'Failed to remove item',
        'cartTotal' => $_SESSION['cart']['total'] ?? 0,
        'itemCount' => $itemCount
    ]);
} else {
    // Redirect back to cart page
    header('Location: /cart');
}
exit; 