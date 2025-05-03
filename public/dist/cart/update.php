<?php
// Start the session
session_start();

// Check if product ID and quantity are provided via POST or GET
$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

// If not found in POST, try GET
if (!$productId) {
    $productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
}

if (!$quantity || $quantity < 1) {
    $quantity = filter_input(INPUT_GET, 'quantity', FILTER_VALIDATE_INT);
}

// Ensure valid quantity
if ($quantity < 1) {
    $quantity = 1;
} elseif ($quantity > 99) {
    $quantity = 99;
}

// Initialize variables
$productName = '';
$found = false;

// Debug information
error_log("Updating cart item: Product ID={$productId}, Quantity={$quantity}");
error_log("Cart before update: " . print_r($_SESSION['cart'], true));

// Check if the cart exists
if (isset($_SESSION['cart']) && isset($_SESSION['cart']['items']) && $productId > 0 && $quantity > 0) {
    // Loop through cart items to find and update the product
    foreach ($_SESSION['cart']['items'] as &$item) {
        error_log("Comparing product_id {$productId} with item id " . (isset($item['id']) ? $item['id'] : 'undefined'));
        
        if (isset($item['id']) && (int)$item['id'] === (int)$productId) {
            $productName = $item['name'];
            $item['quantity'] = $quantity;
            $found = true;
            error_log("Match found! Updated quantity for item {$item['name']} to {$quantity}");
            break;
        }
    }
    unset($item); // Clear the reference to avoid accidental modifications
    
    // Recalculate cart total
    $total = 0;
    foreach ($_SESSION['cart']['items'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    $_SESSION['cart']['total'] = $total;
    
    error_log("Cart after update: " . print_r($_SESSION['cart'], true));
    
    if ($found) {
        // Set success message
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'text' => $productName . ' quantity was updated to ' . $quantity . '.'
        ];
    } else {
        // Set error message
        $_SESSION['flash_message'] = [
            'type' => 'warning',
            'text' => 'Product not found in your cart.'
        ];
    }
} else {
    // Set error message
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'text' => 'Invalid request or empty cart.'
    ];
}

// Redirect back to cart page
header('Location: ../cart');
exit; 