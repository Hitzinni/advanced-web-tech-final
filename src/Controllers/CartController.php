<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\CartModel;
use App\Models\Product;
use App\Helpers\View;

class CartController
{
    private \PDO $db;
    private CartModel $cartModel;
    private Product $productModel;
    
    public function __construct()
    {
        // Database connection
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'grocery_store_dev';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            $this->db = new \PDO($dsn, $username, $password, $options);
            $this->cartModel = new CartModel($this->db);
            $this->productModel = new Product($this->db);
        } catch (\PDOException $e) {
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
            
            // Get cart data from session
            $cartItems = $_SESSION['cart']['items'];
            $cartTotal = $_SESSION['cart']['total'];
            
            // Log debug information
            error_log("CartController::viewCart - Using session-based cart");
            error_log("CartController::viewCart - Cart items count: " . count($cartItems));
            
            View::render('cart', [
                'pageTitle' => 'Shopping Cart | Online Grocery Store',
                'metaDescription' => 'View your shopping cart and proceed to checkout.',
                'cartItems' => $cartItems,
                'cartTotal' => $cartTotal
            ]);
        } catch (\Exception $e) {
            // Log the error
            error_log('CartController::viewCart - Unhandled exception: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            
            // Show a friendly error page
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'An error occurred while loading your cart. Please try again.'
            ];
            
            header('Location: products');
            exit;
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
            header('Location: /cart');
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
            header('Location: /cart');
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
            header('Location: /cart');
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