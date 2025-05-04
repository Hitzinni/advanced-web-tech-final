<?php
// Start the session
session_start();

// Set content type to JSON
header('Content-Type: application/json');

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

// Define base path if not already defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(dirname(__FILE__))));
}

// Include database connection
require_once BASE_PATH . '/config.php';

// Connect to database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit;
}

// Get product details from database
try {
    $stmt = $pdo->prepare("SELECT id, name, price, category, image_url FROM product WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
} catch (PDOException $e) {
    error_log("Error fetching product: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error fetching product details']);
    exit;
}

// Try to add to database cart
try {
    // First, get or create the user's cart
    $userId = (int)$_SESSION['user_id'];
    
    // Get the cart ID
    $stmt = $pdo->prepare('SELECT id FROM cart WHERE user_id = ?');
    $stmt->execute([$userId]);
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cart) {
        $cartId = (int)$cart['id'];
    } else {
        // Create a new cart
        $stmt = $pdo->prepare('INSERT INTO cart (user_id) VALUES (?)');
        $stmt->execute([$userId]);
        $cartId = (int)$pdo->lastInsertId();
    }
    
    // Check if the item already exists in the cart
    $stmt = $pdo->prepare('SELECT id, quantity FROM cart_item WHERE cart_id = ? AND product_id = ?');
    $stmt->execute([$cartId, $productId]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item) {
        // If the item exists, update its quantity
        $newQuantity = (int)$item['quantity'] + $quantity;
        $stmt = $pdo->prepare('UPDATE cart_item SET quantity = ? WHERE id = ?');
        $stmt->execute([$newQuantity, $item['id']]);
    } else {
        // If the item does not exist, insert it as a new cart item
        $stmt = $pdo->prepare('INSERT INTO cart_item (cart_id, product_id, quantity) VALUES (?, ?, ?)');
        $stmt->execute([$cartId, $productId, $quantity]);
    }
} catch (PDOException $e) {
    // Log error but continue with session cart
    error_log("Error adding to database cart: " . $e->getMessage());
}

// Add to session cart - this ensures the cart works even if DB operations fail
$existingItem = false;
if (isset($_SESSION['cart']['items']) && is_array($_SESSION['cart']['items'])) {
    foreach ($_SESSION['cart']['items'] as &$item) {
        if (isset($item['id']) && (int)$item['id'] === (int)$productId) {
            // Update quantity
            $item['quantity'] += $quantity;
            $existingItem = true;
            break;
        }
    }
    unset($item); // Clear reference
}

// Add new item to cart if it doesn't exist
if (!$existingItem) {
    // Ensure category is a string
    $category = $product['category'];
    if (is_array($category)) {
        $category = !empty($category) ? (string)reset($category) : 'General';
    } else {
        $category = (string)$category;
    }
    
    $_SESSION['cart']['items'][] = [
        'id' => (int)$product['id'],
        'name' => $product['name'],
        'category' => $category,
        'price' => (float)$product['price'],
        'quantity' => $quantity,
        'image_url' => $product['image_url']
    ];
}

// Recalculate cart total
$total = 0;
$itemCount = 0;
foreach ($_SESSION['cart']['items'] as $item) {
    $total += (float)$item['price'] * (int)$item['quantity'];
    $itemCount += (int)$item['quantity'];
}
$_SESSION['cart']['total'] = $total;

// Set success message
$_SESSION['flash_message'] = [
    'type' => 'success',
    'text' => $product['name'] . ' was added to your cart.'
];

// Return JSON response
echo json_encode([
    'success' => true,
    'message' => 'Item added to cart successfully',
    'itemCount' => $itemCount,
    'total' => $total
]);
exit; 