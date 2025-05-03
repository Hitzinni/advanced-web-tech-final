<?php
/**
 * Emergency Cart Display - Bypasses all routing and template systems
 * This is a fallback when the main cart page doesn't work
 */

// Set up environment
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define basic constants
define('BASE_PATH', dirname(__DIR__));

// Set up empty cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        'items' => [],
        'total' => 0
    ];
}

// Add a sample item if cart is empty
if (empty($_SESSION['cart']['items'])) {
    $_SESSION['cart']['items'][] = [
        'id' => 1,
        'name' => 'Sample Product',
        'price' => 19.99,
        'quantity' => 1,
        'category' => 'Vegetables'
    ];
    $_SESSION['cart']['total'] = 19.99;
}

// Get cart data
$cartItems = $_SESSION['cart']['items'];
$cartTotal = $_SESSION['cart']['total'];
$cartCount = count($cartItems);

// Simple URL generator function
function getBaseUrl() {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . '://' . $host;
    
    // Get path to public directory
    $scriptPath = $_SERVER['SCRIPT_NAME'];
    $dirParts = explode('/', dirname($scriptPath));
    array_pop($dirParts); // Remove the last part (public)
    $path = implode('/', $dirParts) . '/public/';
    
    return $baseUrl . $path;
}

$baseUrl = getBaseUrl();

// Utility function to create a URL
function url($path) {
    global $baseUrl;
    return $baseUrl . $path;
}

// Action handlers
$action = $_GET['action'] ?? '';
$redirect = false;

if ($action === 'remove' && isset($_GET['id'])) {
    $removeId = (int)$_GET['id'];
    $newItems = [];
    $newTotal = 0;
    
    foreach ($_SESSION['cart']['items'] as $item) {
        if ($item['id'] != $removeId) {
            $newItems[] = $item;
            $itemPrice = $item['price'] ?? 0;
            $itemQty = $item['quantity'] ?? 1;
            $newTotal += $itemPrice * $itemQty;
        }
    }
    
    $_SESSION['cart']['items'] = $newItems;
    $_SESSION['cart']['total'] = $newTotal;
    $redirect = true;
} 
else if ($action === 'update' && isset($_GET['id']) && isset($_GET['qty'])) {
    $updateId = (int)$_GET['id'];
    $updateQty = (int)$_GET['qty'];
    $newTotal = 0;
    
    foreach ($_SESSION['cart']['items'] as &$item) {
        if ($item['id'] == $updateId) {
            $item['quantity'] = $updateQty;
        }
        $itemPrice = $item['price'] ?? 0;
        $itemQty = $item['quantity'] ?? 1;
        $newTotal += $itemPrice * $itemQty;
    }
    
    $_SESSION['cart']['total'] = $newTotal;
    $redirect = true;
}
else if ($action === 'clear') {
    $_SESSION['cart'] = [
        'items' => [],
        'total' => 0
    ];
    $redirect = true;
}

// Redirect after actions
if ($redirect) {
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Update variables after action
$cartItems = $_SESSION['cart']['items'];
$cartTotal = $_SESSION['cart']['total'];
$cartCount = count($cartItems);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Online Grocery Store</title>
    <meta name="description" content="View your shopping cart and proceed to checkout.">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        .cart-header { background-color: #0d6efd; color: white; padding: 2rem; border-radius: 6px; margin-bottom: 2rem; }
        .alert-notice { border-left: 4px solid #0d6efd; padding-left: 1rem; background: #f8f9fa; }
    </style>
</head>
<body>
    <header class="bg-primary text-white py-2">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="<?= url('index.php') ?>" class="text-decoration-none text-white">
                    <h1 class="h4 mb-0">Online Grocery Store</h1>
                </a>
                <nav>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= url('index.php') ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= url('index.php?route=products') ?>">Browse Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= url('index.php?route=about') ?>">About Us</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container py-4">
        <div class="alert alert-notice mb-4">
            <p><strong>Note:</strong> You're viewing the emergency backup cart page. This is a reliable alternative when the main cart has issues.</p>
        </div>
        
        <div class="cart-header">
            <h1 class="mb-2">Shopping Cart</h1>
            <p class="lead mb-0">You have <?= $cartCount ?> item(s) in your cart</p>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="row justify-content-center my-5">
                <div class="col-md-10 col-lg-8">
                    <div class="card shadow border-0">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-cart-x text-primary" style="font-size: 4rem;"></i>
                            <h3 class="mt-4 mb-3">Your cart is empty</h3>
                            <p class="text-muted mb-4">Browse our products to add items to your cart.</p>
                            <a href="<?= url('index.php?route=products') ?>" class="btn btn-primary px-4 py-2">
                                <i class="bi bi-bag-plus me-2"></i>Browse Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Cart Items (<?= $cartCount ?>)</h5>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>?action=clear" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash me-1"></i>Clear Cart
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-center">Price</th>
                                            <th class="text-center">Quantity</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cartItems as $item): 
                                            $itemId = $item['id'] ?? 0;
                                            $itemName = $item['name'] ?? 'Unknown Product';
                                            $itemPrice = $item['price'] ?? 0;
                                            $itemQuantity = $item['quantity'] ?? 1;
                                            $itemTotal = $itemPrice * $itemQuantity;
                                            $category = $item['category'] ?? 'Other';
                                            
                                            // Get appropriate icon
                                            $iconClass = 'box';
                                            if ($category === 'Fruits') $iconClass = 'apple';
                                            if ($category === 'Vegetables') $iconClass = 'flower3';
                                            if ($category === 'Bakery') $iconClass = 'bread-slice';
                                            if ($category === 'Dairy') $iconClass = 'egg-fried';
                                            if ($category === 'Beverages') $iconClass = 'cup-hot';
                                        ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3 bg-light rounded p-2 text-center" style="width: 50px; height: 50px;">
                                                            <i class="bi bi-<?= $iconClass ?> text-primary" style="font-size: 1.2rem; line-height: 32px;"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0"><?= htmlspecialchars($itemName) ?></h6>
                                                            <span class="badge bg-secondary"><?= htmlspecialchars($category) ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">$<?= number_format($itemPrice, 2) ?></td>
                                                <td class="text-center align-middle">
                                                    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="get" class="d-flex justify-content-center align-items-center">
                                                        <input type="hidden" name="action" value="update">
                                                        <input type="hidden" name="id" value="<?= $itemId ?>">
                                                        <input type="number" name="qty" value="<?= $itemQuantity ?>" min="1" max="99" 
                                                            class="form-control form-control-sm text-center" style="width: 60px;">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary ms-2">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td class="text-center align-middle">$<?= number_format($itemTotal, 2) ?></td>
                                                <td class="text-center align-middle">
                                                    <a href="<?= $_SERVER['PHP_SELF'] ?>?action=remove&id=<?= $itemId ?>" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span class="fw-bold">$<?= number_format($cartTotal, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span class="text-success">Free</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold">Total:</span>
                                <span class="fw-bold fs-5">$<?= number_format($cartTotal, 2) ?></span>
                            </div>
                            <a href="<?= url('index.php?route=checkout') ?>" class="btn btn-success w-100">
                                <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                            </a>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="<?= url('index.php?route=products') ?>" class="btn btn-outline-primary w-100">
                            <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                        </a>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5>Try alternative cart pages:</h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <a href="<?= url('cart_direct.php') ?>">
                                        <i class="bi bi-arrow-right-circle me-2"></i>Standard alternative cart
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="<?= url('index.php?route=cart&no_template=true') ?>">
                                        <i class="bi bi-arrow-right-circle me-2"></i>Basic cart without template
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= url('cart_fix.php') ?>">
                                        <i class="bi bi-wrench me-2"></i>Troubleshooting page
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-dark text-white p-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Online Grocery Store</h5>
                    <p>The best selection of fresh groceries.</p>
                    <a href="<?= url('index.php') ?>" class="btn btn-sm btn-light me-2">Home</a>
                    <a href="<?= url('index.php?route=products') ?>" class="btn btn-sm btn-light me-2">Products</a>
                    <a href="<?= url('index.php?route=about') ?>" class="btn btn-sm btn-light">About</a>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <p>&copy; <?= date('Y') ?> Advanced Web Technologies</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 