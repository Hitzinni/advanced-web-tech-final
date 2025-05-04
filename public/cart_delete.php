<?php
// Start the session
session_start();

// Enable detailed error logging
ini_set('display_errors', 0); // Don't show errors to user
error_log("Direct cart delete script called");

// Get the product ID from GET or POST
$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$productId) {
    $productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
}

// Log the product ID
error_log("Direct cart delete - Product ID: " . ($productId ?? 'null'));
error_log("Direct cart delete - Original cart state: " . json_encode($_SESSION['cart'] ?? []));

// Validate product ID
if (!$productId) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'text' => 'Invalid product ID provided.'
    ];
    header('Location: index.php?route=cart');
    exit;
}

// Remove item from session cart
$removed = false;
$removedItemName = '';

if (isset($_SESSION['cart']) && isset($_SESSION['cart']['items']) && is_array($_SESSION['cart']['items'])) {
    $cartItems = $_SESSION['cart']['items'];
    $updatedItems = [];
    $cartTotal = 0;
    
    error_log("Direct cart delete - Original items count: " . count($cartItems));
    
    foreach ($cartItems as $item) {
        if (isset($item['id']) && (int)$item['id'] == $productId) {
            $removed = true;
            $removedItemName = $item['name'] ?? 'Product';
            error_log("Direct cart delete - Removing item: " . $removedItemName . " (ID: " . $productId . ")");
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
    
    error_log("Direct cart delete - New items count: " . count($updatedItems));
    
    // Force session write to ensure changes are saved
    session_write_close();
    session_start();
    
    error_log("Direct cart delete - Final cart state: " . json_encode($_SESSION['cart']));
}

// Try to remove from database directly if user is logged in
if (isset($_SESSION['user_id'])) {
    try {
        // Define base path if not already defined
        define('BASE_PATH', dirname(__DIR__));
        
        // Include necessary config
        require_once BASE_PATH . '/config.php';
        
        // Connect to database
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // First get the cart ID
        $userId = (int)$_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        $cartRow = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($cartRow && isset($cartRow['id'])) {
            $cartId = (int)$cartRow['id'];
            // Delete the item directly
            $stmt = $pdo->prepare("DELETE FROM cart_item WHERE cart_id = ? AND product_id = ?");
            $result = $stmt->execute([$cartId, $productId]);
            error_log("Direct cart delete - Database removal result: " . ($result ? 'success' : 'failed') . ", rows: " . $stmt->rowCount());
            
            if ($stmt->rowCount() > 0) {
                $removed = true;
            }
        }
    } catch (Exception $e) {
        error_log("Direct cart delete - Error in database operation: " . $e->getMessage());
    }
}

// Set flash message
$_SESSION['flash_message'] = [
    'type' => $removed ? 'success' : 'danger',
    'text' => $removed ? ($removedItemName . ' was removed from your cart.') : 'Failed to remove item from cart.'
];

// Calculate total quantity
$itemCount = 0;
if (isset($_SESSION['cart']['items']) && is_array($_SESSION['cart']['items'])) {
    foreach ($_SESSION['cart']['items'] as $item) {
        $itemCount += isset($item['quantity']) ? (int)$item['quantity'] : 1;
    }
}

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// If AJAX request, return JSON
if ($isAjax) {
    header('Content-Type: application/json');
    $response = [
        'success' => $removed,
        'message' => $removed ? 'Item removed successfully' : 'Failed to remove item',
        'cartTotal' => $_SESSION['cart']['total'] ?? 0,
        'itemCount' => $itemCount
    ];
    echo json_encode($response);
} else {
    // Force session write before redirect
    session_write_close();
    
    // Redirect to cart page with cache busting
    header('Location: index.php?route=cart&t=' . time());
}
exit; 