<?php
declare(strict_types=1);
session_start();

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Autoload classes
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

// Set JSON header
header('Content-Type: application/json');

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Debug log
error_log('AJAX add to cart - Request method: ' . $_SERVER['REQUEST_METHOD']);
error_log('AJAX add to cart - User ID: ' . ($_SESSION['user_id'] ?? 'not set'));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'You must be logged in to add items to your cart',
        'redirect' => '/login'
    ]);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

try {
    // Get database name from environment or use the development DB name explicitly
    $dbName = $_ENV['DB_NAME'] ?? 'grocery_store_dev';
    error_log('AJAX add to cart - Using database: ' . $dbName);
    
    // Initialize database connection with explicit params
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
    $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $db = new \PDO($dsn, $user, $pass, $options);
    error_log('AJAX add to cart - Database connection established');
    
    // Initialize models needed
    require_once BASE_PATH . '/src/models/CartModel.php';
    require_once BASE_PATH . '/src/models/Product.php';
    $cartModel = new \App\Models\CartModel($db);
    $productModel = new \App\Models\Product($db);
    
    // Get product ID and quantity
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
    
    error_log('AJAX add to cart - Product ID: ' . ($productId ?? 'not set') . ', Quantity: ' . ($quantity ?? 'not set'));
    
    if (!$productId) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }
    
    // Check if product exists
    $product = $productModel->getById($productId);
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    error_log('AJAX add to cart - Product found: ' . $product['name']);
    
    // Add to cart
    $userId = (int)$_SESSION['user_id'];
    $cartId = $cartModel->getOrCreateCart($userId);
    error_log('AJAX add to cart - Cart ID: ' . $cartId);
    
    $success = $cartModel->addItem($cartId, $productId, $quantity);
    
    if ($success) {
        $itemCount = $cartModel->getCartItemCount($userId);
        error_log('AJAX add to cart - Item added successfully, new count: ' . $itemCount);
        echo json_encode([
            'success' => true, 
            'message' => 'Item added to cart successfully',
            'itemCount' => $itemCount
        ]);
    } else {
        error_log('AJAX add to cart - Failed to add item');
        echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
    }
} catch (\Exception $e) {
    error_log('AJAX add to cart - Error: ' . $e->getMessage());
    error_log('AJAX add to cart - Stack trace: ' . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding the item to your cart']);
} 