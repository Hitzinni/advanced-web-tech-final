<?php
// Standalone cart page that doesn't depend on the template system
declare(strict_types=1);

// Start session
session_start();

// Define the base path
define('BASE_PATH', dirname(__DIR__));

// Create a simple cart item if there's none (for testing)
if (!isset($_SESSION['cart']) || empty($_SESSION['cart']['items'])) {
    $_SESSION['cart'] = [
        'items' => [
            [
                'id' => 1,
                'name' => 'Sample Product',
                'price' => 19.99,
                'quantity' => 1,
                'category' => 'Vegetables'
            ]
        ],
        'total' => 19.99
    ];
}

// Get cart data from session
$cartItems = $_SESSION['cart']['items'];
$cartTotal = $_SESSION['cart']['total'];
$itemCount = count($cartItems);

// Utility function to generate URLs
function getBaseUrl() {
    // Always use HTTPS regardless of current protocol
    $protocol = 'https';
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME']; 
    $pathInfo = pathinfo($scriptName);
    $dirname = str_replace('\\', '/', $pathInfo['dirname']);
    $base = $protocol . '://' . $host . $dirname;
    return rtrim($base, '/') . '/';
}

function url($route, $params = []) {
    $baseUrl = getBaseUrl();
    $url = $baseUrl . $route;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Online Grocery Store</title>
    <meta name="description" content="View your shopping cart and proceed to checkout.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS to match main site -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        header.bg-primary {
            background-color: #0d6efd !important;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .card {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: none;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eaeaea;
            padding: 15px;
        }
        /* Style to match the site's product page blue header */
        .product-header {
            background-color: #0d6efd;
            color: white;
            padding: 2rem;
            border-radius: 6px;
            margin-bottom: 2rem;
        }
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
                            <a class="nav-link text-white" href="<?= url('index.php', ['route' => 'products']) ?>">Browse Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= url('index.php', ['route' => 'about']) ?>">About Us</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white fw-bold" href="<?= url('cart_direct.php') ?>">
                                <i class="bi bi-cart3 me-1"></i>Cart
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container py-4">
        <!-- Product-page style header -->
        <div class="product-header mb-4">
            <h1 class="mb-2">Shopping Cart</h1>
            <p class="lead mb-0">Review your items and proceed to checkout</p>
        </div>

        <?php if (empty($cartItems)): ?>
            <!-- Empty Cart Display -->
            <div class="row justify-content-center my-5">
                <div class="col-md-10 col-lg-8">
                    <div class="card shadow border-0 overflow-hidden">
                        <div class="card-header bg-light py-3">
                            <h2 class="h5 mb-0"><i class="bi bi-cart3 me-2"></i>Shopping Cart</h2>
                        </div>
                        <div class="card-body text-center py-5">
                            <div class="py-4">
                                <i class="bi bi-cart-x text-primary" style="font-size: 5rem;"></i>
                                <h3 class="mt-4 mb-3">Your cart is empty</h3>
                                <p class="text-muted mb-4 px-4 mx-auto" style="max-width: 500px;">
                                    Looks like you haven't added any products to your cart yet. 
                                    Browse our selection and find something you like!
                                </p>
                                <a href="<?= url('index.php', ['route' => 'products']) ?>" class="btn btn-primary px-4 py-2">
                                    <i class="bi bi-bag-plus me-2"></i>Browse Products
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Cart Items -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow border-0 overflow-hidden mb-4">
                        <div class="card-header bg-light py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h2 class="h5 mb-0">
                                    <i class="bi bi-cart3 me-2"></i>Cart Items (<span id="cart-item-count"><?= $itemCount ?></span>)
                                </h2>
                                <a href="<?= url('index.php', ['route' => 'api/cart/clear']) ?>" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash me-1"></i>Clear Cart
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50%">Product</th>
                                            <th scope="col" class="text-center">Price</th>
                                            <th scope="col" class="text-center">Quantity</th>
                                            <th scope="col" class="text-center">Total</th>
                                            <th scope="col" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($cartItems as $item) {
                                            $itemId = isset($item['id']) ? (int)$item['id'] : 0;
                                            $itemName = isset($item['name']) ? htmlspecialchars((string)$item['name']) : 'Unknown Product';
                                            $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
                                            $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
                                            $itemTotal = $itemPrice * $itemQuantity;
                                            $formattedPrice = number_format($itemPrice, 2);
                                            $formattedTotal = number_format($itemTotal, 2);
                                            
                                            $category = $item['category'] ?? 'Other';
                                            
                                            // Replace match expression with switch statement for PHP 7.2 compatibility
                                            switch ($category) {
                                                case 'Fruits':
                                                    $iconClass = 'apple';
                                                    break;
                                                case 'Vegetables':
                                                    $iconClass = 'flower3';
                                                    break;
                                                case 'Bakery':
                                                    $iconClass = 'bread-slice';
                                                    break;
                                                case 'Dairy':
                                                    $iconClass = 'egg-fried';
                                                    break;
                                                case 'Beverages':
                                                    $iconClass = 'cup-hot';
                                                    break;
                                                default:
                                                    $iconClass = 'box';
                                                    break;
                                            }
                                            ?>
                                            <tr class="cart-item" data-item-id="<?= $itemId ?>">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-3">
                                                            <div class="bg-light rounded text-center p-2" style="width: 60px; height: 60px;">
                                                                <i class="bi bi-<?= $iconClass ?> text-primary" style="font-size: 1.5rem; line-height: 40px;"></i>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1"><?= $itemName ?></h6>
                                                            <span class="badge bg-info text-white"><?= $category ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">$<?= $formattedPrice ?></td>
                                                <td class="text-center align-middle">
                                                    <div class="d-inline-block">
                                                        <form action="<?= url('index.php', ['route' => 'api/cart/update']) ?>" method="POST" class="d-flex align-items-center">
                                                            <input type="hidden" name="product_id" value="<?= $itemId ?>">
                                                            <input type="number" name="quantity" value="<?= $itemQuantity ?>" min="1" max="99" 
                                                                class="form-control form-control-sm text-center" style="width: 60px;">
                                                            <button type="submit" class="btn btn-sm btn-outline-secondary ms-2">
                                                                <i class="bi bi-arrow-repeat"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle item-total">
                                                    $<?= $formattedTotal ?>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <a href="<?= url('index.php', ['route' => 'api/cart/remove', 'product_id' => $itemId]) ?>" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?= url('index.php', ['route' => 'products']) ?>" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow border-0 mb-4">
                        <div class="card-header bg-light py-3">
                            <h2 class="h5 mb-0">
                                <i class="bi bi-receipt me-2"></i>Order Summary
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span class="fw-bold" id="cart-subtotal">$<?= number_format($cartTotal, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span class="fw-bold text-success">Free</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="h5">Total:</span>
                                <span class="h5 fw-bold" id="cart-total">$<?= number_format($cartTotal, 2) ?></span>
                            </div>
                            <a href="<?= url('index.php', ['route' => 'checkout']) ?>" class="btn btn-success w-100 btn-lg">
                                <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                            </a>
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
                    <p>The best selection of fresh vegetables and meats.</p>
                    <div>
                        <a href="<?= url('index.php') ?>" class="btn btn-sm btn-light me-2">Home</a>
                        <a href="<?= url('index.php', ['route' => 'products']) ?>" class="btn btn-sm btn-light me-2">Products</a>
                        <a href="<?= url('index.php', ['route' => 'about']) ?>" class="btn btn-sm btn-light me-2">About Us</a>
                        <a href="<?= url('cart_direct.php') ?>" class="btn btn-sm btn-light me-2">Cart</a>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?= date('Y') ?> Advanced Web Technologies</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 