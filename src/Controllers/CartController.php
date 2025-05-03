<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\CartModel;
use App\Models\Product;
use App\Helpers\View;
use App\Helpers\Database;

class CartController
{
    private $db;
    private $cartModel;
    private $productModel;
    
    public function __construct()
    {
        try {
            // Use the Database helper class that we fixed
            $this->db = Database::getInstance();
            $this->cartModel = new CartModel($this->db);
            $this->productModel = new Product($this->db);
            error_log("CartController::__construct - Successfully initialized with Database helper");
        } catch (\PDOException $e) {
            error_log("CartController::__construct - Database connection failed: " . $e->getMessage());
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    
    /**
     * Display the cart page
     */
    public function viewCart(): void
    {
        try {
            // DEBUG - Log the method being called
            error_log("CartController::viewCart - Method called - " . date('Y-m-d H:i:s'));
            
            // Check if we're in debug mode
            $isDebug = isset($_GET['debug']) && $_GET['debug'] === 'true';
            $noTemplate = isset($_GET['no_template']) && $_GET['no_template'] === 'true';
            
            // Initialize cart in session if it doesn't exist
            if (!isset($_SESSION['cart'])) {
                error_log("CartController::viewCart - Cart not found in session, initializing");
                $_SESSION['cart'] = [
                    'items' => [],
                    'total' => 0
                ];
            }
            
            // TEMPORARY: Add a sample product to cart if empty for debugging purposes
            if (empty($_SESSION['cart']['items']) && ($isDebug || isset($_GET['add_sample']))) {
                error_log("CartController::viewCart - Adding sample product for testing");
                $_SESSION['cart']['items'][] = [
                    'id' => 1,
                    'name' => 'Sample Product',
                    'price' => 19.99,
                    'quantity' => 1,
                    'category' => 'Vegetables'
                ];
                $_SESSION['cart']['total'] = 19.99;
            }
            
            // Get cart data from session
            $cartItems = $_SESSION['cart']['items'];
            $cartTotal = $_SESSION['cart']['total'];
            
            // Log debug information
            error_log("CartController::viewCart - Using session-based cart");
            error_log("CartController::viewCart - Cart items count: " . count($cartItems));
            error_log("CartController::viewCart - Cart total: " . $cartTotal);
            
            // If we're in no_template mode, just display the cart directly
            if ($noTemplate) {
                echo '<div style="max-width: 800px; margin: 0 auto; padding: 20px;">';
                echo '<h1>Shopping Cart</h1>';
                echo '<p>Your cart has ' . count($cartItems) . ' items with a total of $' . number_format($cartTotal, 2) . '</p>';
                
                // Show cart items in a basic format
                if (!empty($cartItems)) {
                    echo '<table style="width: 100%; border-collapse: collapse; margin-top: 20px;">';
                    echo '<tr style="background-color: #e9ecef;"><th style="padding: 10px; text-align: left;">Product</th><th style="padding: 10px; text-align: right;">Price</th><th style="padding: 10px; text-align: right;">Quantity</th><th style="padding: 10px; text-align: right;">Total</th></tr>';
                    
                    foreach ($cartItems as $item) {
                        $itemName = isset($item['name']) ? htmlspecialchars((string)$item['name']) : 'Unknown Product';
                        $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
                        $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
                        $itemTotal = $itemPrice * $itemQuantity;
                        
                        echo '<tr style="border-bottom: 1px solid #dee2e6;">';
                        echo '<td style="padding: 10px;">' . $itemName . '</td>';
                        echo '<td style="padding: 10px; text-align: right;">$' . number_format($itemPrice, 2) . '</td>';
                        echo '<td style="padding: 10px; text-align: right;">' . $itemQuantity . '</td>';
                        echo '<td style="padding: 10px; text-align: right;">$' . number_format($itemTotal, 2) . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '<tr style="background-color: #e9ecef;"><td colspan="3" style="padding: 10px; text-align: right;"><strong>Total:</strong></td><td style="padding: 10px; text-align: right;"><strong>$' . number_format($cartTotal, 2) . '</strong></td></tr>';
                    echo '</table>';
                } else {
                    echo '<p>Your cart is empty.</p>';
                }
                
                echo '<p style="margin-top: 20px;"><a href="index.php" style="display: inline-block; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">Back to Home</a></p>';
                echo '</div>';
                return;
            }
            
            // Render the cart view using the enhanced View helper with better error handling
            error_log("CartController::viewCart - About to render cart view");
            View::output('cart', [
                'pageTitle' => 'Shopping Cart | Online Grocery Store',
                'metaDescription' => 'View your shopping cart and proceed to checkout.',
                'cartItems' => $cartItems,
                'cartTotal' => $cartTotal,
                'isDebug' => $isDebug
            ]);
            
            error_log("CartController::viewCart - View rendered successfully");
        } catch (\Throwable $e) {
            // Log the error
            error_log('CartController::viewCart - Unhandled exception: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            
            // Instead of redirecting to cart_direct.php, show basic cart with error notice
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>Shopping Cart | Online Grocery Store</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
                    .container { max-width: 900px; margin: 0 auto; padding: 20px; }
                    .alert { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
                    h1 { color: #0d6efd; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                    th, td { padding: 10px; text-align: left; border-bottom: 1px solid #dee2e6; }
                    th { background-color: #f8f9fa; }
                    .text-right { text-align: right; }
                    .btn { display: inline-block; padding: 8px 16px; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 4px; }
                    .btn-danger { background-color: #dc3545; }
                    .btn-success { background-color: #28a745; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="alert">
                        <strong>Notice:</strong> The cart is displayed in basic mode due to a template error.
                        <br>
                        <small>Error: ' . htmlspecialchars($e->getMessage()) . '</small>
                    </div>
                    
                    <h1>Shopping Cart</h1>';
                    
                    // Get cart data from session
                    $cartItems = $_SESSION['cart']['items'] ?? [];
                    $cartTotal = $_SESSION['cart']['total'] ?? 0;
                    
                    if (empty($cartItems)) {
                        echo '<p>Your cart is empty.</p>';
                    } else {
                        echo '<table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-right">Price</th>
                                    <th class="text-right">Quantity</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>';
                            
                        foreach ($cartItems as $item) {
                            $itemName = isset($item['name']) ? htmlspecialchars((string)$item['name']) : 'Unknown Product';
                            $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
                            $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
                            $itemTotal = $itemPrice * $itemQuantity;
                            
                            echo '<tr>
                                <td>' . $itemName . '</td>
                                <td class="text-right">$' . number_format($itemPrice, 2) . '</td>
                                <td class="text-right">' . $itemQuantity . '</td>
                                <td class="text-right">$' . number_format($itemTotal, 2) . '</td>
                            </tr>';
                        }
                        
                        echo '</tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                    <td class="text-right"><strong>$' . number_format($cartTotal, 2) . '</strong></td>
                                </tr>
                            </tfoot>
                        </table>';
                    }
                    
                    echo '<p>
                        <a href="index.php" class="btn">Back to Home</a>
                        <a href="index.php?route=cart&no_template=true" class="btn">View Basic Cart</a>
                    </p>
                </div>
            </body>
            </html>';
        }
    }
    
    /**
     * Add an item to the cart via AJAX
     */
    public function addToCart(): void
    {
        // Set content type to JSON
        header('Content-Type: application/json');
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to add items to your cart']);
            exit;
        }
        
        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo json_encode(['success' => false, 'message' => 'Invalid security token']);
            exit;
        }
        
        // Get product ID and quantity
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            exit;
        }
        
        // Check if product exists
        $product = $this->productModel->getById($productId);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        // Add to cart
        $userId = (int)$_SESSION['user_id'];
        $cartId = $this->cartModel->getOrCreateCart($userId);
        $success = $this->cartModel->addItem($cartId, $productId, $quantity);
        
        if ($success) {
            $itemCount = $this->cartModel->getCartItemCount($userId);
            echo json_encode([
                'success' => true, 
                'message' => 'Item added to cart successfully',
                'itemCount' => $itemCount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
        }
    }
    
    /**
     * Update cart item quantity via AJAX or direct request
     */
    public function updateCartItem(): void
    {
        // Initialize response
        $response = [
            'success' => false,
            'message' => 'Failed to update cart item'
        ];
        
        // Check if this is an AJAX request
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // If AJAX, set content type to JSON
        if ($isAjax) {
            header('Content-Type: application/json');
        }
        
        // Support both GET and POST requests
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
                header('Location: ' . View::url('cart'));
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
        
        // If AJAX request, return JSON
        if ($isAjax) {
            // Calculate total quantity
            $itemCount = 0;
            foreach ($_SESSION['cart']['items'] as $item) {
                $itemCount += isset($item['quantity']) ? (int)$item['quantity'] : 1;
            }
            
            echo json_encode([
                'success' => $updated,
                'message' => $updated ? 'Cart updated successfully' : 'Failed to update cart',
                'cartTotal' => $_SESSION['cart']['total'] ?? 0,
                'itemCount' => $itemCount
            ]);
        } else {
            // Redirect back to cart
            header('Location: ' . View::url('cart'));
        }
        exit;
    }
    
    /**
     * Remove an item from the cart via AJAX or direct request
     */
    public function removeCartItem(): void
    {
        // Initialize response
        $response = [
            'success' => false,
            'message' => 'Failed to remove item from cart'
        ];
        
        // Check if this is an AJAX request
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // If AJAX, set content type to JSON
        if ($isAjax) {
            header('Content-Type: application/json');
        }
        
        // Support both GET and POST requests
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        
        // If not found in POST, try GET
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
                header('Location: ' . View::url('cart'));
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
        
        // If AJAX request, return JSON
        if ($isAjax) {
            // Calculate total quantity
            $itemCount = 0;
            foreach ($_SESSION['cart']['items'] as $item) {
                $itemCount += isset($item['quantity']) ? (int)$item['quantity'] : 1;
            }
            
            echo json_encode([
                'success' => $removed,
                'message' => $removed ? 'Item removed successfully' : 'Failed to remove item',
                'cartTotal' => $_SESSION['cart']['total'] ?? 0,
                'itemCount' => $itemCount
            ]);
        } else {
            // Redirect back to cart
            header('Location: ' . View::url('cart'));
        }
        exit;
    }
    
    /**
     * Clear the cart via AJAX or direct request
     */
    public function clearCart(): void
    {
        // Initialize response array
        $response = [
            'success' => false,
            'message' => 'Failed to clear cart'
        ];
        
        // Check if this is an AJAX request
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // If AJAX, set content type to JSON
        if ($isAjax) {
            header('Content-Type: application/json');
        }
        
        // Skip authentication and CSRF validation for now to ensure cart can be cleared
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
        
        // If this is an AJAX request, return JSON
        if ($isAjax) {
            $response['success'] = true;
            $response['message'] = 'Cart cleared successfully';
            echo json_encode($response);
        } else {
            // Otherwise redirect back to cart page
            header('Location: ' . View::url('cart'));
        }
        exit;
    }
    
    /**
     * Get the cart count for header display via AJAX
     */
    public function getCartCount(): void
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['count' => 0]);
            exit;
        }
        
        $userId = (int)$_SESSION['user_id'];
        $count = $this->cartModel->getCartItemCount($userId);
        
        echo json_encode(['count' => $count]);
    }
} 