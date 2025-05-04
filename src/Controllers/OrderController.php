<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;
use App\Models\Order;
use App\Models\Product;
use App\Models\CartModel;
use App\Middleware\AuthMiddleware;
use App\Middleware\CSRFProtection;
use App\Helpers\Validators;

class OrderController
{
    private $orderModel;
    private $productModel;
    private $db;
    private $cartModel;
    
    public function __construct()
    {
        // Database connection
        $host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $dbname = defined('DB_NAME') ? DB_NAME : 'grocery_store_dev';
        $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
        $username = defined('DB_USER') ? DB_USER : 'root';
        $password = defined('DB_PASS') ? DB_PASS : '';
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            $this->db = new \PDO($dsn, $username, $password, $options);
            $this->orderModel = new Order();
            $this->productModel = new Product();
            $this->cartModel = new CartModel($this->db);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    
    /**
     * Legacy create method from direct product order
     * Now redirects to checkout
     */
    public function create(): void
    {
        // Redirect to checkout page
        header('Location: checkout');
        exit;
    }
    
    /**
     * Display the checkout page
     */
    public function checkout(): void
    {
        // This simply redirects to the checkout page view
        // Actual checkout logic and validation is done in the view
        
        $userAddress = null;
        $userData = null;
        
        // If user is logged in, get their default shipping address
        if (isset($_SESSION['user_id'])) {
            try {
                // Get user data
                $userData = $this->db->prepare("SELECT name, email, phone FROM user WHERE id = ?");
                $userData->execute([(int)$_SESSION['user_id']]);
                $userData = $userData->fetch(\PDO::FETCH_ASSOC);
                
                // Check if shipping_address table exists
                $tableCheck = $this->db->query("SHOW TABLES LIKE 'shipping_address'");
                $shippingTableExists = $tableCheck->rowCount() > 0;
                
                if ($shippingTableExists) {
                    // Get default shipping address if available
                    $stmt = $this->db->prepare("SELECT * FROM shipping_address WHERE user_id = ? AND is_default = 1 LIMIT 1");
                    $stmt->execute([(int)$_SESSION['user_id']]);
                    $userAddress = $stmt->fetch(\PDO::FETCH_ASSOC);
                    
                    // If no default address, get the most recent one
                    if (!$userAddress) {
                        $stmt = $this->db->prepare("SELECT * FROM shipping_address WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
                        $stmt->execute([(int)$_SESSION['user_id']]);
                        $userAddress = $stmt->fetch(\PDO::FETCH_ASSOC);
                    }
                }
            } catch (\PDOException $e) {
                // Log the error but continue without address data
                error_log("Error fetching shipping address: " . $e->getMessage());
            }
        }
        
        View::output('checkout', [
            'pageTitle' => 'Checkout',
            'metaDescription' => 'Complete your purchase and checkout',
            'userAddress' => $userAddress,
            'userData' => $userData
        ]);
    }
    
    /**
     * Process checkout form
     */
    public function processCheckout(): void
    {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'Invalid form submission.'
            ];
            header('Location: checkout');
            exit;
        }
        
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'text' => 'Please login to proceed with checkout.'
            ];
            header('Location: login');
            exit;
        }
        
        // Ensure cart is not empty
        if (!isset($_SESSION['cart']) || !isset($_SESSION['cart']['items']) || empty($_SESSION['cart']['items'])) {
            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'text' => 'Your cart is empty. Please add products before checkout.'
            ];
            header('Location: cart');
            exit;
        }
        
        // Calculate cart totals with shipping fee logic
        $cartItems = $_SESSION['cart']['items'];
        $subtotal = 0;
        
        foreach ($cartItems as $item) {
            $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
            $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
            $subtotal += $itemPrice * $itemQuantity;
        }
        
        // Apply shipping fee logic - $5 shipping fee for orders under $25
        $shippingFee = ($subtotal < 25) ? 5.00 : 0.00;
        $total = $subtotal + $shippingFee;
        
        // Get payment method
        $paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'cod';
        
        // Check if we need to save the shipping address
        $saveAddress = isset($_POST['save_address']) && $_POST['save_address'] == '1';
        
        // Validate required fields - basic info always required
        $requiredFields = [
            'first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip'
        ];
        
        // Add card fields if payment method is card
        if ($paymentMethod === 'card') {
            $requiredFields = array_merge($requiredFields, ['card_name', 'card_number', 'exp_date', 'cvv']);
        }
        
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty($_POST[$field])) {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'Please fill in all required fields.'
                ];
                header('Location: checkout');
                exit;
            }
        }
        
        // Save shipping address if requested
        if ($saveAddress && isset($_SESSION['user_id'])) {
            try {
                // Check if shipping_address table exists
                $tableCheck = $this->db->query("SHOW TABLES LIKE 'shipping_address'");
                $shippingTableExists = $tableCheck->rowCount() > 0;
                
                if ($shippingTableExists) {
                    // Prepare address data
                    $addressData = [
                        'user_id' => (int)$_SESSION['user_id'],
                        'first_name' => htmlspecialchars($_POST['first_name']),
                        'last_name' => htmlspecialchars($_POST['last_name']),
                        'address' => htmlspecialchars($_POST['address']),
                        'city' => htmlspecialchars($_POST['city']),
                        'state' => htmlspecialchars($_POST['state']),
                        'zip' => htmlspecialchars($_POST['zip']),
                        'phone' => htmlspecialchars($_POST['phone']),
                        'is_default' => 1 // Set as default
                    ];
                    
                    // Check if user already has addresses
                    $stmt = $this->db->prepare("SELECT COUNT(*) FROM shipping_address WHERE user_id = ?");
                    $stmt->execute([(int)$_SESSION['user_id']]);
                    $hasAddresses = (int)$stmt->fetchColumn() > 0;
                    
                    if ($hasAddresses) {
                        // Unset current default addresses
                        $stmt = $this->db->prepare("UPDATE shipping_address SET is_default = 0 WHERE user_id = ?");
                        $stmt->execute([(int)$_SESSION['user_id']]);
                    }
                    
                    // Insert new address
                    $stmt = $this->db->prepare("INSERT INTO shipping_address 
                        (user_id, first_name, last_name, address, city, state, zip, phone, is_default) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $addressData['user_id'],
                        $addressData['first_name'],
                        $addressData['last_name'],
                        $addressData['address'],
                        $addressData['city'],
                        $addressData['state'],
                        $addressData['zip'],
                        $addressData['phone'],
                        $addressData['is_default']
                    ]);
                }
            } catch (\PDOException $e) {
                // Just log the error but continue with order processing
                error_log("Error saving shipping address: " . $e->getMessage());
            }
        }
        
        // Prepare payment method display text
        $paymentMethodText = $paymentMethod === 'cod' ? 'Cash on Delivery' : 'Credit Card';
        
        // Prepare order data
        $orderData = [
            'user_id' => (int)$_SESSION['user_id'],
            'total_amount' => $total,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'shipping_address' => htmlspecialchars($_POST['address'] . ', ' . $_POST['city'] . ', ' . $_POST['state'] . ' ' . $_POST['zip']),
            'payment_method' => $paymentMethodText,
            'items' => $cartItems
        ];
        
        // Create the order
        try {
            // Check if orders table exists
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'orders'");
            $ordersTableExists = $tableCheck->rowCount() > 0;
            
            if ($ordersTableExists) {
                // Use the new createOrder method for multiple items
                $orderId = $this->orderModel->createOrder($orderData);
            } else {
                // Fallback to legacy method - just use the first item
                $firstItem = reset($cartItems);
                $orderId = $this->orderModel->create(
                    (int)$_SESSION['user_id'],
                    (int)$firstItem['id'],
                    (float)$total
                );
            }
            
            if ($orderId) {
                // Store order ID in session for receipt page
                $_SESSION['last_order_id'] = $orderId;
                
                // Clear the cart
                $_SESSION['cart'] = [
                    'items' => [],
                    'total' => 0,
                    'subtotal' => 0,
                    'shipping' => 0
                ];
                
                // Set success message
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'text' => 'Your order has been placed successfully!'
                ];
                
                // Redirect to order receipt
                header('Location: order-receipt?id=' . $orderId);
                exit;
            } else {
                throw new \Exception("Failed to create order");
            }
        } catch (\Exception $e) {
            // Log the error
            error_log("Order creation failed: " . $e->getMessage());
            
            // Set error message
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'Failed to place your order. Please try again.'
            ];
            
            // Redirect back to checkout
            header('Location: checkout');
            exit;
        }
    }
    
    /**
     * Show order receipt
     */
    public function show(): void
    {
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'text' => 'Please login to view your order.'
            ];
            header('Location: login');
            exit;
        }
        
        // Get order ID from query string
        $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$orderId && isset($_SESSION['last_order_id'])) {
            $orderId = (int)$_SESSION['last_order_id'];
        }
        
        if (!$orderId) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'No order specified.'
            ];
            header('Location: my-orders');
            exit;
        }
        
        // Get order details
        $order = $this->orderModel->getById($orderId);
        
        if (!$order || $order['user_id'] !== $_SESSION['user_id']) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'Order not found or you do not have permission to view it.'
            ];
            header('Location: my-orders');
            exit;
        }
        
        // Display order receipt
        View::output('order-receipt', [
            'pageTitle' => 'Order Receipt #' . $orderId,
            'metaDescription' => 'Receipt for your order #' . $orderId,
            'order' => $order
        ]);
    }
    
    /**
     * Display the user's order history
     */
    public function myOrders(): void
    {
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'text' => 'Please login to view your orders.'
            ];
            header('Location: ' . View::url('login'));
            exit;
        }
        
        try {
            // Enhanced error logging
            error_log('OrderController::myOrders - Starting method for user ID: ' . $_SESSION['user_id']);
            
            // Get user's orders
            $orders = $this->orderModel->getByUserId((int)$_SESSION['user_id']);
            error_log('OrderController::myOrders - Found ' . count($orders) . ' orders');
            
            // Group orders by date for the view
            $groupedOrders = [];
            foreach ($orders as $order) {
                // Skip invalid orders
                if (!isset($order['ordered_at'])) {
                    error_log("Invalid order data found: " . json_encode($order));
                    continue;
                }
                
                $orderDate = date('Y-m-d', strtotime($order['ordered_at']));
                
                if (!isset($groupedOrders[$orderDate])) {
                    $groupedOrders[$orderDate] = [];
                }
                
                // For new format orders, add a summary for display
                if (isset($order['is_new_format']) && $order['is_new_format']) {
                    $order['price_at_order'] = $order['total_amount'] ?? 0; // For compatibility with view
                    $order['product_name'] = 'Order #' . ($order['id'] ?? 'Unknown') . ' (' . ($order['item_count'] ?? 0) . ' items)';
                    $order['category'] = $order['payment_method'] ?? 'Standard';
                    $order['image_url'] = 'images/products/order-icon.jpg'; // Default image for multi-item orders
                    
                    // Add total field for compatibility with view if it doesn't exist
                    if (!isset($order['total'])) {
                        $order['total'] = $order['total_amount'] ?? 0;
                    }
                    
                    // Add default status if not set
                    if (!isset($order['status'])) {
                        $order['status'] = 'completed';
                    }
                    
                    // Load order items if available and needed for display
                    if (!isset($order['items']) && isset($order['id'])) {
                        try {
                            $fullOrder = $this->orderModel->getById((int)$order['id']);
                            if ($fullOrder && isset($fullOrder['items'])) {
                                $order['items'] = $fullOrder['items'];
                            } else {
                                $order['items'] = [];
                            }
                        } catch (\Exception $itemError) {
                            error_log("Error loading order items: " . $itemError->getMessage());
                            $order['items'] = [];
                        }
                    }
                }
                
                $groupedOrders[$orderDate][] = $order;
            }
            
            // Sort by date descending (newest first)
            krsort($groupedOrders);
            
            // Check if the view file exists before rendering
            $viewPath = BASE_PATH . '/src/views/my-orders.php';
            if (!file_exists($viewPath)) {
                error_log("OrderController::myOrders - View file not found: $viewPath");
                throw new \Exception("View file 'my-orders.php' not found");
            }
            error_log("OrderController::myOrders - View file exists: $viewPath");
            
            // Display orders
            error_log("OrderController::myOrders - Rendering view with " . count($groupedOrders) . " grouped date entries");
            View::output('my-orders', [
                'pageTitle' => 'My Orders',
                'metaDescription' => 'View your order history',
                'orders' => $orders,
                'groupedOrders' => $groupedOrders
            ]);
        } catch (\Exception $e) {
            // Log the error with more details
            error_log('Error in OrderController::myOrders: ' . $e->getMessage());
            error_log('Exception trace: ' . $e->getTraceAsString());
            
            // Check if headers are already sent
            if (!headers_sent()) {
                // Display error message to user
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'An error occurred while loading your orders: ' . $e->getMessage()
                ];
                
                // Redirect to home page
                header('Location: ' . View::url('home') . '');
                exit;
            } else {
                // Output error directly if headers already sent
                echo '<div class="alert alert-danger">';
                echo '<h3>Error Loading Orders</h3>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<p><a href="' . View::url('home') . '" class="btn btn-primary">Return to Home</a></p>';
                echo '</div>';
            }
        }
    }
    
    /**
     * Update order status
     */
    public function updateStatus(): void
    {
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'text' => 'Please login to update order status.'
            ];
            header('Location: login');
            exit;
        }
        
        // Get order ID and new status from query string
        $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $newStatus = isset($_GET['status']) ? $_GET['status'] : '';
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';
        
        if (!$orderId || !$newStatus) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'Missing order ID or status.'
            ];
            header('Location: my-orders');
            exit;
        }
        
        // Validate status
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'received', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'Invalid order status.'
            ];
            header('Location: order-receipt?id=' . $orderId);
            exit;
        }
        
        try {
            // Check if the user is a manager
            $isManager = isset($_SESSION['role']) && $_SESSION['role'] === 'manager';
            
            // Get the order to verify user permissions
            $order = $this->orderModel->getById($orderId);
            
            if (!$order) {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'Order not found.'
                ];
                header('Location: my-orders');
                exit;
            }
            
            // Verify the user has permission to update this order
            $canUpdate = false;
            
            if ($isManager) {
                // Managers can update any order status
                $canUpdate = true;
            } elseif ($order['user_id'] == $_SESSION['user_id']) {
                // Regular users can only update their own orders
                if ($newStatus === 'received') {
                    // Allow regular users to mark their orders as received from any status
                    $canUpdate = true;
                } elseif ($newStatus === 'cancelled' && $order['status'] === 'pending') {
                    // Allow regular users to cancel their orders if they're still pending
                    $canUpdate = true;
                }
            }
            
            if (!$canUpdate) {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'You do not have permission to update this order status.'
                ];
                header('Location: my-orders');
                exit;
            }
            
            // Use the model method to update status
            $result = $this->orderModel->updateStatus($orderId, $newStatus);
            
            if ($result) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'text' => 'Order status updated to ' . ucfirst($newStatus) . '.'
                ];
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'Failed to update order status.'
                ];
            }
        } catch (\PDOException $e) {
            error_log("Error updating order status: " . $e->getMessage());
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'An error occurred while updating order status.'
            ];
        }
        
        // Redirect based on source
        if ($redirect === 'manager') {
            header('Location: manager-orders');
        } else {
            // Default redirect to order receipt
            header('Location: order-receipt?id=' . $orderId);
        }
        exit;
    }
} 