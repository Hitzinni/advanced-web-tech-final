<?php
// Start the session
session_start();

// Demo product data (must match the data in add-to-cart.php)
$products = [
    1 => [
        'id' => 1,
        'name' => 'Fresh Apples',
        'category' => 'Fruits',
        'price' => 4.99,
        'image' => 'images/products/fruit-1.jpg',
        'description' => 'Crisp and juicy apples locally sourced.'
    ],
    2 => [
        'id' => 2,
        'name' => 'Farm Fresh Eggs',
        'category' => 'Dairy',
        'price' => 5.49,
        'image' => 'images/products/dairy-1.jpg',
        'description' => 'Organic free-range eggs from local farms.'
    ],
    3 => [
        'id' => 3,
        'name' => 'Organic Coffee',
        'category' => 'Beverages',
        'price' => 12.99,
        'image' => 'images/products/beverages-1.jpg',
        'description' => 'Premium roasted coffee beans, ethically sourced.'
    ],
    4 => [
        'id' => 4,
        'name' => 'Whole Wheat Bread',
        'category' => 'Bakery',
        'price' => 3.99,
        'image' => 'images/products/bakery-1.jpg',
        'description' => 'Freshly baked whole grain bread.'
    ],
    5 => [
        'id' => 5,
        'name' => 'Organic Broccoli',
        'category' => 'Vegetables',
        'price' => 2.49,
        'image' => 'images/products/vegetables-1.jpg',
        'description' => 'Farm-fresh organic broccoli.'
    ]
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Products | Online Grocery Store</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Header -->
    <header class="bg-primary text-white py-2">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="index.php" class="text-decoration-none">
                    <img src="images/logo.svg" alt="Grocery Store" height="40" class="d-inline-block align-top">
                </a>
                <nav>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white active" href="products-demo.php">Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="cart/">
                                <i class="bi bi-cart3 me-1"></i>Cart
                                <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart']['items'])): ?>
                                <span class="badge bg-danger rounded-pill"><?= count($_SESSION['cart']['items']) ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container py-4">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash_message']['text']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <!-- Products Header -->
        <div class="category-header position-relative mb-4 bg-primary text-white p-4 rounded shadow">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="fw-bold mb-2"><i class="bi bi-basket3 me-2"></i>Demo Products</h1>
                    <p class="lead mb-0">Click "Add to Cart" to see the cart functionality</p>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm product-card">
                    <div class="bg-light text-center p-4">
                        <i class="bi bi-<?= $product['category'] === 'Fruits' ? 'apple' : 
                                       ($product['category'] === 'Vegetables' ? 'flower3' : 
                                       ($product['category'] === 'Bakery' ? 'bread-slice' : 
                                       ($product['category'] === 'Dairy' ? 'egg-fried' : 'cup-hot'))) ?> 
                           text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <span class="badge bg-info"><?= htmlspecialchars($product['category']) ?></span>
                        </div>
                        <p class="card-text text-muted"><?= htmlspecialchars($product['description']) ?></p>
                        <p class="fw-bold text-primary fs-5">$<?= number_format($product['price'], 2) ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <form action="cart/add-to-cart.php" method="GET" class="d-flex">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="number" name="quantity" value="1" min="1" max="99" class="form-control form-control-sm me-2" style="width: 70px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white p-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Online Grocery Store</h5>
                    <p>The best selection of fresh products.</p>
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