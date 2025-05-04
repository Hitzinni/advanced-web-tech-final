<?php
// Start the session
session_start();

// Set content type to JSON and prevent caching
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Enable error logging, disable display errors to user
ini_set('display_errors', 0);
error_log("Clear cart API called: " . json_encode($_POST) . " | GET: " . json_encode($_GET));

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
         strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Failed to clear cart'
];

try {
    // Clear the cart in session
    $_SESSION['cart'] = [
        'items' => [],
        'total' => 0
    ];

    // Try to clear from database cart if user is logged in
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
                
                // Delete all items from cart_item
                $stmt = $pdo->prepare('DELETE FROM cart_item WHERE cart_id = ?');
                $stmt->execute([$cartId]);
            }
        } catch (PDOException $e) {
            // Log error but continue - we've already cleared the session cart
            error_log("Error clearing database cart: " . $e->getMessage());
        }
    }

    // Set success message
    $_SESSION['flash_message'] = [
        'type' => 'success',
        'text' => 'Your cart has been cleared.'
    ];

    // Update response
    $response['success'] = true;
    $response['message'] = 'Cart cleared successfully';

    // If this is an AJAX request, return JSON
    if ($isAjax) {
        echo json_encode($response);
        error_log("API Response: " . json_encode($response));
    } else {
        // Otherwise redirect back to cart page with relative path
        header('Location: ../../index.php?route=cart');
    }
} catch (Exception $e) {
    // Log any unexpected errors
    error_log("Unexpected error in clear.php: " . $e->getMessage());
    
    // Return error JSON
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage(),
    ]);
}
exit; 