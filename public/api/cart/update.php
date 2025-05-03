<?php
// Start the session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Get product ID and quantity from GET or POST
$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

// If not found in POST, try GET
if (!$productId) {
    $productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
}

if (!$quantity || $quantity < 1) {
    $quantity = filter_input(INPUT_GET, 'quantity', FILTER_VALIDATE_INT);
}

// Validate parameters
if (!$productId || !$quantity || $quantity < 1) {
    if ($isAjax) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'text' => 'Invalid parameters provided.'
        ];
        header('Location: /cart');
        exit;
    }
}

// Update cart in session
$updated = false;

if (isset($_SESSION['cart']) && isset($_SESSION['cart']['items']) && is_array($_SESSION['cart']['items'])) {
    $cartItems = &$_SESSION['cart']['items']; // Reference to modify original
    $cartTotal = 0;
    
    foreach ($cartItems as &$item) {
        if (isset($item['id']) && $item['id'] == $productId) {
            $item['quantity'] = $quantity;
            $updated = true;
        }
        
        // Recalculate total
        $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
        $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
        $cartTotal += $itemPrice * $itemQuantity;
    }
    
    // Update cart total
    $_SESSION['cart']['total'] = $cartTotal;
}

// Set success message
$_SESSION['flash_message'] = [
    'type' => $updated ? 'success' : 'danger',
    'text' => $updated ? 'Cart updated successfully.' : 'Failed to update cart.'
];

// Calculate total quantity for response
$itemCount = 0;
foreach ($_SESSION['cart']['items'] as $item) {
    $itemCount += isset($item['quantity']) ? (int)$item['quantity'] : 1;
}

// Return JSON response if it's an AJAX request
if ($isAjax) {
    echo json_encode([
        'success' => $updated,
        'message' => $updated ? 'Cart updated successfully' : 'Failed to update cart',
        'cartTotal' => $_SESSION['cart']['total'] ?? 0,
        'itemCount' => $itemCount
    ]);
} else {
    // Redirect back to cart page
    header('Location: /cart');
}
exit; 