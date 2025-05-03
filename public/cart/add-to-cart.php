<?php
// Start the session
session_start();

// Initialize cart in session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        'items' => [],
        'total' => 0
    ];
}

// Check if product ID is provided
$productId = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;

// Ensure valid quantity
if ($quantity < 1) {
    $quantity = 1;
}

// Check if we have a product in session from browse.php page
$mainProductFound = false;
$mainProduct = null;

// Check if the request is coming from the main product catalog
if (isset($_SESSION['current_products']) && is_array($_SESSION['current_products'])) {
    foreach ($_SESSION['current_products'] as $sessionProduct) {
        if (isset($sessionProduct['id']) && (int)$sessionProduct['id'] === (int)$productId) {
            $mainProduct = $sessionProduct;
            $mainProductFound = true;
            error_log("Found product in current_products: " . print_r($mainProduct, true));
            break;
        }
    }
}

// If not found in current_products, check if this is a single product from the product detail page
if (!$mainProductFound && isset($_SESSION['current_product']) && is_array($_SESSION['current_product'])) {
    $sessionProduct = $_SESSION['current_product'];
    if (isset($sessionProduct['id']) && (int)$sessionProduct['id'] === (int)$productId) {
        $mainProduct = $sessionProduct;
        $mainProductFound = true;
        error_log("Found product in current_product: " . print_r($mainProduct, true));
    }
}

// For debugging - log the session data related to products
error_log("Product ID being searched: " . $productId);
error_log("Session current_product: " . (isset($_SESSION['current_product']) ? json_encode($_SESSION['current_product']) : 'Not set'));
error_log("Session current_products count: " . (isset($_SESSION['current_products']) ? count($_SESSION['current_products']) : 'Not set'));

// Add product to cart if it exists
if ($mainProductFound) {
    // Use product from the main catalog
    $product = [
        'id' => $mainProduct['id'],
        'name' => $mainProduct['name'],
        'category' => $mainProduct['category'] ? (
            is_array($mainProduct['category']) 
                ? (empty($mainProduct['category']) ? 'General' : (string)reset($mainProduct['category'])) 
                : (string)$mainProduct['category']
        ) : 'General',
        'price' => (float)$mainProduct['price'],
        'image_url' => $mainProduct['image_url'] ?? 'images/products/placeholder.jpg'
    ];
    
    error_log("Processing product for cart: " . print_r($product, true));
} else {
    // Try to get the product directly from the database as a last resort
    require_once '../../src/models/Product.php';
    
    try {
        $db = new PDO(
            "mysql:host=" . ($_ENV['DB_HOST'] ?? 'localhost') . 
            ";dbname=" . ($_ENV['DB_NAME'] ?? 'grocery_store_dev') . 
            ";charset=" . ($_ENV['DB_CHARSET'] ?? 'utf8mb4'),
            $_ENV['DB_USER'] ?? 'root',
            $_ENV['DB_PASS'] ?? ''
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create product model and get product
        $productModel = new \App\Models\Product($db);
        $dbProduct = $productModel->getById((int)$productId);
        
        if ($dbProduct) {
            error_log("Found product in database: " . print_r($dbProduct, true));
            
            $product = [
                'id' => $dbProduct['id'],
                'name' => $dbProduct['name'],
                'category' => $dbProduct['category'] ? (
                    is_array($dbProduct['category']) 
                        ? (empty($dbProduct['category']) ? 'General' : (string)reset($dbProduct['category'])) 
                        : (string)$dbProduct['category']
                ) : 'General',
                'price' => (float)$dbProduct['price'],
                'image_url' => $dbProduct['image_url'] ?? 'images/products/placeholder.jpg'
            ];
        } else {
            // Product not found in database either
            $product = null;
            error_log("Product ID {$productId} not found in database");
        }
    } catch (Exception $e) {
        // Error connecting to database or fetching product
        error_log("Error fetching product from database: " . $e->getMessage());
        $product = null;
    }
}

// Check if this is an AJAX request
$wantsJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

if ($product) {
    // Check if product already in cart
    $existingItem = false;
    foreach ($_SESSION['cart']['items'] as &$item) {
        if (isset($item['id']) && (int)$item['id'] === (int)$productId) {
            // Update quantity
            $item['quantity'] += $quantity;
            $existingItem = true;
            error_log("Updated existing item in cart. New quantity: {$item['quantity']}");
            break;
        }
    }
    unset($item); // Clear reference properly to avoid issues
    
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
            'id' => $product['id'],
            'name' => $product['name'],
            'category' => $category,
            'price' => $product['price'],
            'quantity' => $quantity,
            'image_url' => $product['image_url']
        ];
        error_log("Added new item to cart");
    }
    
    // Recalculate cart total
    $total = 0;
    foreach ($_SESSION['cart']['items'] as $item) {
        $total += (float)$item['price'] * (int)$item['quantity'];
    }
    $_SESSION['cart']['total'] = $total;
    error_log("Recalculated cart total: {$total}");
    
    // Count total items (accounting for quantity)
    $itemCount = 0;
    foreach ($_SESSION['cart']['items'] as $item) {
        $itemCount += isset($item['quantity']) ? (int)$item['quantity'] : 1;
    }
    
    // Set success message
    $_SESSION['flash_message'] = [
        'type' => 'success',
        'text' => $product['name'] . ' was added to your cart.'
    ];
    
    // If this is a JSON/AJAX request, return success response
    if ($wantsJson) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $product['name'] . ' was added to your cart.',
            'product' => [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ],
            'cart' => [
                'total' => $total,
                'itemCount' => $itemCount,
                'uniqueItems' => count($_SESSION['cart']['items'])
            ]
        ]);
        exit;
    }
    
    // Redirect back to referring page if available
    $referrer = $_SERVER['HTTP_REFERER'] ?? '';
    
    if (strpos($referrer, 'products') !== false) {
        header('Location: ' . $referrer);
        exit;
    }
} else {
    // Set error message
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'text' => 'Invalid product selection. Please select a product from the catalog.'
    ];
    
    // If this is a JSON/AJAX request, return error response
    if ($wantsJson) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid product selection. Please select a product from the catalog.'
        ]);
        exit;
    }
}

// Default redirect to cart page
header('Location: ../cart/');
exit; 