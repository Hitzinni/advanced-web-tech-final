<!-- Checkout Page Header Section -->
<div class="category-header position-relative mb-4 bg-primary text-white p-4 rounded shadow">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h1 class="fw-bold mb-2"><i class="bi bi-credit-card me-2"></i>Checkout</h1>
            <p class="lead mb-0">Complete your purchase</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="home" class="text-white">Home</a></li>
                    <li class="breadcrumb-item"><a href="products" class="text-white">Products</a></li>
                    <li class="breadcrumb-item"><a href="cart" class="text-white">Cart</a></li>
                    <li class="breadcrumb-item active text-white-50" aria-current="page">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row">
    <!-- Order Summary -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light py-3">
                <h2 class="h5 fw-bold mb-0">
                    <i class="bi bi-basket me-2"></i>Order Summary
                </h2>
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <?php 
                                    // Get safe values with defaults
                                    $itemName = isset($item['name']) ? htmlspecialchars($item['name']) : 'Unknown Product';
                                    $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
                                    $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
                                    $itemTotal = $itemPrice * $itemQuantity;
                                    
                                    // Handle category
                                    $category = 'Other';
                                    if (isset($item['category'])) {
                                        if (is_array($item['category'])) {
                                            $category = !empty($item['category']) ? reset($item['category']) : 'Other';
                                        } else {
                                            $category = (string)$item['category'];
                                        }
                                    }
                                    $categoryDisplay = htmlspecialchars($category);
                                    
                                    // Get image URL and handle path properly
                                    $imagePath = isset($item['image_url']) ? (string)$item['image_url'] : 'images/products/placeholder.jpg';
                                    
                                    // Remove 'public/' prefix if it exists
                                    if (strpos($imagePath, 'public/') === 0) {
                                        $imagePath = substr($imagePath, 7); // Remove 'public/'
                                    }
                                    
                                    // Remove leading slash if present
                                    if (strpos($imagePath, '/') === 0) {
                                        $imagePath = substr($imagePath, 1);
                                    }
                                    
                                    // Set icon based on category - keeping this for fallback
                                    $iconClass = 'box';
                                    if ($category === 'Fruits') {
                                        $iconClass = 'apple';
                                    } elseif ($category === 'Vegetables') {
                                        $iconClass = 'flower3';
                                    } elseif ($category === 'Bakery') {
                                        $iconClass = 'bread-slice';
                                    } elseif ($category === 'Dairy') {
                                        $iconClass = 'egg-fried';
                                    } elseif ($category === 'Beverages') {
                                        $iconClass = 'cup-hot';
                                    }
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="bg-light rounded overflow-hidden" style="width: 50px; height: 50px;">
                                                    <img src="<?= $imagePath ?>" alt="<?= $itemName ?>" class="w-100 h-100" style="object-fit: cover;" 
                                                         onerror="this.onerror=null; this.src='images/products/placeholder.jpg'; this.parentNode.innerHTML='<div class=\'text-center p-2\'><i class=\'bi bi-<?= $iconClass ?> text-primary\' style=\'font-size: 1.5rem; line-height: 30px;\'></i></div>'">
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-1"><?= $itemName ?></h6>
                                                <span class="badge bg-info text-white"><?= $categoryDisplay ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">$<?= number_format($itemPrice, 2) ?></td>
                                    <td class="text-center align-middle"><?= $itemQuantity ?></td>
                                    <td class="text-center align-middle">
                                        $<?= number_format($itemTotal, 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Section -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light py-3">
                <h2 class="h5 fw-bold mb-0">
                    <i class="bi bi-receipt me-2"></i>Order Details
                </h2>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span class="fw-bold">$<?= number_format($cartTotal, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping:</span>
                    <span class="fw-bold text-success">Free</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <span class="h5">Total:</span>
                    <span class="h5 fw-bold">$<?= number_format($cartTotal, 2) ?></span>
                </div>
                
                <form action="process-checkout" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <div class="mb-4">
                        <h3 class="h6 fw-bold mb-3">Payment Method</h3>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="payment-cash" value="cash" checked>
                            <label class="form-check-label" for="payment-cash">
                                <i class="bi bi-cash me-2"></i>Cash on Delivery
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <div>
                            <small>This is a demo checkout. No real payment will be processed.</small>
                        </div>
                    </div>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Place Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="d-grid">
            <a href="cart" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Return to Cart
            </a>
        </div>
    </div>
</div> 