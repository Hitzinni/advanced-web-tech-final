<?php
// Start the session
session_start();

// Set content type to JSON and prevent caching
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Enable error logging, disable display errors to user
ini_set('display_errors', 0);
error_log("Remove API called: " . json_encode($_POST) . " | GET: " . json_encode($_GET));

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Get the product ID from GET or POST
$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$productId) {
    $productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
}

// Log the product ID
error_log("Product ID to remove: " . ($productId ?? 'null'));

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
        header('Location: ../../index.php?route=cart');
        exit;
    }
}

// Remove item from session cart
$removed = false;
$removedItemName = '';

try {
    if (isset($_SESSION['cart']) && isset($_SESSION['cart']['items']) && is_array($_SESSION['cart']['items'])) {
        $cartItems = $_SESSION['cart']['items'];
        $updatedItems = [];
        $cartTotal = 0;
        
        foreach ($cartItems as $item) {
            if (isset($item['id']) && (int)$item['id'] == $productId) {
                $removed = true;
                $removedItemName = $item['name'] ?? 'Product';
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

    // Try to remove from database cart if user is logged in
    if (isset($_SESSION['user_id'])) {
        try {
            // Define base path if not already defined
            if (!defined('BASE_PATH')) {
                define('BASE_PATH', dirname(dirname(dirname(__FILE__))));
            }

            // Include database connection
            require_once BASE_PATH . '/config.php';

            // Connect to database
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Get cart ID
            $userId = (int)$_SESSION['user_id'];
            $stmt = $pdo->prepare('SELECT id FROM cart WHERE user_id = ?');
            $stmt->execute([$userId]);
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($cart) {
                $cartId = (int)$cart['id'];
                
                // Delete the item from cart_item
                $stmt = $pdo->prepare('DELETE FROM cart_item WHERE cart_id = ? AND product_id = ?');
                $stmt->execute([$cartId, $productId]);
            }
        } catch (PDOException $e) {
            // Log error but continue - we've already removed from session
            error_log("Error removing item from database cart: " . $e->getMessage());
        }
    }

    // Set flash message
    $_SESSION['flash_message'] = [
        'type' => $removed ? 'success' : 'danger',
        'text' => $removed ? ($removedItemName . ' was removed from your cart.') : 'Failed to remove item from cart.'
    ];

    // Calculate total quantity
    $itemCount = 0;
    foreach ($_SESSION['cart']['items'] as $item) {
        $itemCount += isset($item['quantity']) ? (int)$item['quantity'] : 1;
    }

    // If AJAX request, return JSON
    if ($isAjax) {
        $response = [
            'success' => $removed,
            'message' => $removed ? 'Item removed successfully' : 'Failed to remove item',
            'cartTotal' => $_SESSION['cart']['total'] ?? 0,
            'itemCount' => $itemCount
        ];
        echo json_encode($response);
        error_log("API Response: " . json_encode($response));
    } else {
        // Redirect to cart page using proper route
        header('Location: ../../index.php?route=cart');
    }
} catch (Exception $e) {
    // Log any unexpected errors
    error_log("Unexpected error in remove.php: " . $e->getMessage());
    
    // Return error JSON
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage(),
    ]);
}
exit; 