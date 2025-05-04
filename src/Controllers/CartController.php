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
            // Initialize cart in session if it doesn't exist
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [
                    'items' => [],
                    'total' => 0
                ];
            }
            
            // First try to get cart data from database if user is logged in
            $cartItems = [];
            $cartTotal = 0;
            
            if (isset($_SESSION['user_id'])) {
                try {
                    // Try to get items from DB
                    $dbCartItems = $this->cartModel->getCartItems((int)$_SESSION['user_id']);
                    
                    if (!empty($dbCartItems)) {
                        // Transform DB items to match session format
                        foreach ($dbCartItems as $item) {
                            $cartItems[] = [
                                'id' => (int)$item['product_id'],
                                'name' => $item['name'],
                                'price' => (float)$item['price'],
                                'quantity' => (int)$item['quantity'],
                                'category' => $item['category'],
                                'image_url' => $item['image_url']
                            ];
                            
                            $cartTotal += (float)$item['price'] * (int)$item['quantity'];
                        }
                        
                        // Update session cart with DB items
                        $_SESSION['cart'] = [
                            'items' => $cartItems,
                            'total' => $cartTotal
                        ];
                        
                        error_log("CartController::viewCart - Loaded " . count($cartItems) . " items from database");
                    }
                } catch (\Exception $e) {
                    error_log("CartController::viewCart - Error loading from DB: " . $e->getMessage());
                    // Fall back to session cart
                }
            }
            
            // If no DB items, use session cart
            if (empty($cartItems)) {
                $cartItems = $_SESSION['cart']['items'];
                $cartTotal = $_SESSION['cart']['total'];
                error_log("CartController::viewCart - Using session cart with " . count($cartItems) . " items");
            }
            
            // Render the cart view using the enhanced View helper
            View::output('cart', [
                'pageTitle' => 'Shopping Cart | Online Grocery Store',
                'metaDescription' => 'View your shopping cart and proceed to checkout.',
                'cartItems' => $cartItems,
                'cartTotal' => $cartTotal
            ]);
        } catch (\Throwable $e) {
            // Log the error
            error_log('CartController::viewCart - Error: ' . $e->getMessage());
            
            // Show basic cart with error notice
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
        // Set content type to JSON and prevent caching
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        
        try {
            // Log the request for debugging
            error_log('CartController::updateCartItem - Request: ' . json_encode($_POST) . ' | GET: ' . json_encode($_GET));
            
            // Initialize cart in session if it doesn't exist
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [
                    'items' => [],
                    'total' => 0
                ];
            }
            
            // Check if this is an AJAX request
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            // Get product ID and quantity
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
            
            // If not found in POST, try GET
            if (!$productId) {
                $productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
            }
            
            if (!$quantity || $quantity < 1) {
                $quantity = filter_input(INPUT_GET, 'quantity', FILTER_VALIDATE_INT);
            }
            
            // Log the parameters
            error_log('CartController::updateCartItem - Product ID: ' . ($productId ?? 'null') . ', Quantity: ' . ($quantity ?? 'null'));
            
            // Validate parameters
            if (!$productId || !$quantity || $quantity < 1) {
                $response = ['success' => false, 'message' => 'Invalid parameters provided'];
                echo json_encode($response);
                exit;
            }
            
            // Update cart in session
            $updated = false;
            
            if (isset($_SESSION['cart']) && isset($_SESSION['cart']['items']) && is_array($_SESSION['cart']['items'])) {
                $cartItems = &$_SESSION['cart']['items']; // Reference to modify original
                $cartTotal = 0;
                
                foreach ($cartItems as &$item) {
                    if (isset($item['id']) && (int)$item['id'] == $productId) {
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
            
            // Try to update in database if user is logged in
            if (isset($_SESSION['user_id'])) {
                try {
                    $userId = (int)$_SESSION['user_id'];
                    $cartId = $this->cartModel->getOrCreateCart($userId);
                    $success = $this->cartModel->updateItemQuantity($cartId, $productId, $quantity);
                    
                    if ($success) {
                        $updated = true;
                    }
                } catch (\Exception $e) {
                    error_log('CartController::updateCartItem - Error updating in DB: ' . $e->getMessage());
                    // Continue with session cart update
                }
            }
            
            // Set flash message
            $_SESSION['flash_message'] = [
                'type' => $updated ? 'success' : 'danger',
                'text' => $updated ? 'Cart updated successfully.' : 'Failed to update cart.'
            ];
            
            // Calculate total quantity
            $itemCount = 0;
            foreach ($_SESSION['cart']['items'] as $item) {
                $itemCount += isset($item['quantity']) ? (int)$item['quantity'] : 1;
            }
            
            // Return response
            $response = [
                'success' => $updated,
                'message' => $updated ? 'Cart updated successfully' : 'Failed to update cart',
                'cartTotal' => $_SESSION['cart']['total'] ?? 0,
                'itemCount' => $itemCount
            ];
            
            echo json_encode($response);
            error_log('CartController::updateCartItem - Response: ' . json_encode($response));
        } catch (\Exception $e) {
            // Log any errors
            error_log('CartController::updateCartItem - Error: ' . $e->getMessage());
            
            // Return error response
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Remove an item from the cart via AJAX or direct request
     */
    public function removeCartItem(): void
    {
        // Set content type to JSON and prevent caching
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        
        try {
            // Log the request for debugging
            error_log('CartController::removeCartItem - Request: ' . json_encode($_POST) . ' | GET: ' . json_encode($_GET));
            
            // Initialize cart in session if it doesn't exist
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [
                    'items' => [],
                    'total' => 0
                ];
            }
            
            // Check if this is an AJAX request
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            // Get product ID
            $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
            if (!$productId) {
                $productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
            }
            
            // Log the product ID
            error_log('CartController::removeCartItem - Product ID: ' . ($productId ?? 'null'));
            
            if (!$productId) {
                $response = ['success' => false, 'message' => 'Invalid product ID'];
                echo json_encode($response);
                exit;
            }
            
            // Remove item from session cart
            $removed = false;
            $removedItemName = '';
            
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
                    $userId = (int)$_SESSION['user_id'];
                    $success = $this->cartModel->removeItem($userId, $productId);
                    
                    if ($success) {
                        $removed = true;
                    }
                } catch (\Exception $e) {
                    error_log('CartController::removeCartItem - Error removing from DB: ' . $e->getMessage());
                    // Continue with session cart removal
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
            
            // Return response
            $response = [
                'success' => $removed,
                'message' => $removed ? 'Item removed successfully' : 'Failed to remove item',
                'cartTotal' => $_SESSION['cart']['total'] ?? 0,
                'itemCount' => $itemCount
            ];
            
            echo json_encode($response);
            error_log('CartController::removeCartItem - Response: ' . json_encode($response));
        } catch (\Exception $e) {
            // Log any errors
            error_log('CartController::removeCartItem - Error: ' . $e->getMessage());
            
            // Return error response
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Clear the entire cart
     */
    public function clearCart(): void
    {
        // Set content type to JSON and prevent caching
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        
        try {
            // Log the request for debugging
            error_log('CartController::clearCart - Request: ' . json_encode($_POST) . ' | GET: ' . json_encode($_GET));
            
            // Initialize response
            $response = [
                'success' => false,
                'message' => 'Failed to clear cart'
            ];
            
            // Check if this is an AJAX request
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            // Clear session cart
            $_SESSION['cart'] = [
                'items' => [],
                'total' => 0
            ];
            
            // Try to clear database cart if user is logged in
            if (isset($_SESSION['user_id'])) {
                try {
                    $userId = (int)$_SESSION['user_id'];
                    $success = $this->cartModel->clearCart($userId);
                    
                    if (!$success) {
                        error_log('CartController::clearCart - Failed to clear database cart for user: ' . $userId);
                    }
                } catch (\Exception $e) {
                    error_log('CartController::clearCart - Error clearing DB cart: ' . $e->getMessage());
                    // Continue with session cart clearing
                }
            }
            
            // Set flash message
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'Your cart has been cleared.'
            ];
            
            // Update response
            $response['success'] = true;
            $response['message'] = 'Cart cleared successfully';
            
            echo json_encode($response);
            error_log('CartController::clearCart - Response: ' . json_encode($response));
        } catch (\Exception $e) {
            // Log any errors
            error_log('CartController::clearCart - Error: ' . $e->getMessage());
            
            // Return error response
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get the cart count for header display via AJAX
     */
    public function getCartCount(): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        
        try {
            // Default response
            $response = [
                'itemCount' => 0,
                'total' => 0.00
            ];
            
            // Get count from session cart if user not logged in
            if (!isset($_SESSION['user_id'])) {
                if (isset($_SESSION['cart']) && isset($_SESSION['cart']['items'])) {
                    $itemCount = 0;
                    foreach ($_SESSION['cart']['items'] as $item) {
                        $itemCount += isset($item['quantity']) ? (int)$item['quantity'] : 1;
                    }
                    $response['itemCount'] = $itemCount;
                    $response['total'] = isset($_SESSION['cart']['total']) ? (float)$_SESSION['cart']['total'] : 0.00;
                }
                
                echo json_encode($response);
                exit;
            }
            
            // Get data for logged in user
            $userId = (int)$_SESSION['user_id'];
            $count = $this->cartModel->getCartItemCount($userId);
            
            // Get cart total
            $total = 0.00;
            $cartItems = $this->cartModel->getCartItems($userId);
            if (!empty($cartItems)) {
                foreach ($cartItems as $item) {
                    $total += ((float)$item['price'] * (int)$item['quantity']);
                }
            }
            
            $response['itemCount'] = $count;
            $response['total'] = $total;
            
            echo json_encode($response);
        } catch (\Exception $e) {
            error_log('CartController::getCartCount - Error: ' . $e->getMessage());
            echo json_encode(['itemCount' => 0, 'total' => 0.00]);
        }
    }
} 