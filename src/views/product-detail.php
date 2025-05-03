<!-- Product Detail Page -->
<div class="category-header position-relative mb-4 bg-primary text-white p-4 rounded shadow">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h1 class="fw-bold mb-2"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="lead mb-0">Detailed product information</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="home" class="text-white">Home</a></li>
                    <li class="breadcrumb-item"><a href="products" class="text-white">Products</a></li>
                    <?php if (isset($product['category'])): ?>
                    <li class="breadcrumb-item"><a href="products?category=<?= urlencode($product['category']) ?>" class="text-white"><?= htmlspecialchars($product['category']) ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active text-white-50" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row mb-5">
    <!-- Product Image -->
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="card shadow-sm border-0 overflow-hidden">
            <?php
            // Process image path 
            $imagePath = $product['image_url'] ?? 'images/products/placeholder.jpg';
            
            // Remove 'public/' prefix if it exists
            if (strpos($imagePath, 'public/') === 0) {
                $imagePath = substr($imagePath, 7); 
            }
            
            // Remove leading slash if present
            if (strpos($imagePath, '/') === 0) {
                $imagePath = substr($imagePath, 1);
            }
            ?>
            <img src="<?= htmlspecialchars($imagePath) ?>" 
                 class="img-fluid product-detail-img" 
                 alt="<?= htmlspecialchars($product['name']) ?>" 
                 onerror="this.src='images/products/placeholder.jpg'" 
                 style="height: 400px; width: 100%; object-fit: cover;">
        </div>
    </div>
    
    <!-- Product Details -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h3 fw-bold mb-0"><?= htmlspecialchars($product['name']) ?></h2>
                    <span class="badge bg-primary rounded-pill px-3 py-2 fs-5">$<?= number_format((float)$product['price'], 2) ?></span>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($product['category'])): ?>
                <div class="mb-4">
                    <span class="badge bg-info text-white px-3 py-2"><?= htmlspecialchars($product['category']) ?></span>
                </div>
                <?php endif; ?>
                
                <div class="mb-4">
                    <h3 class="h5 fw-bold mb-3">Product Description</h3>
                    <p class="text-muted">
                        <?php if (isset($product['description']) && !empty($product['description'])): ?>
                            <?= nl2br(htmlspecialchars($product['description'])) ?>
                        <?php else: ?>
                            <?= htmlspecialchars($product['name']) ?> is a premium quality product from our <?= htmlspecialchars($product['category'] ?? 'product') ?> selection. We source all our products from trusted local farms and suppliers to ensure the highest quality.
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="mb-4">
                    <h3 class="h5 fw-bold mb-3">Delivery Information</h3>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item bg-transparent px-0 d-flex align-items-center">
                            <i class="bi bi-truck text-primary me-3 fs-4"></i>
                            <div>
                                <h4 class="h6 mb-1 fw-bold">Free Delivery</h4>
                                <p class="text-muted small mb-0">Free delivery on orders over $25</p>
                            </div>
                        </li>
                        <li class="list-group-item bg-transparent px-0 d-flex align-items-center">
                            <i class="bi bi-clock-history text-primary me-3 fs-4"></i>
                            <div>
                                <h4 class="h6 mb-1 fw-bold">Delivery Time</h4>
                                <p class="text-muted small mb-0">Usually delivered within 1-2 business days</p>
                            </div>
                        </li>
                        <li class="list-group-item bg-transparent px-0 d-flex align-items-center">
                            <i class="bi bi-shield-check text-primary me-3 fs-4"></i>
                            <div>
                                <h4 class="h6 mb-1 fw-bold">Freshness Guarantee</h4>
                                <p class="text-muted small mb-0">100% freshness guaranteed or full refund</p>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="d-flex align-items-center mb-4">
                    <div class="input-group me-3" style="max-width: 160px;">
                        <button type="button" class="btn btn-outline-secondary quantity-decrease">
                            <i class="bi bi-dash"></i>
                        </button>
                        <input type="number" id="quantity-input" class="form-control text-center" value="1" min="1" max="99" style="min-width: 60px; width: 60px;">
                        <button type="button" class="btn btn-outline-secondary quantity-increase">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                    <button id="add-to-cart-btn" class="btn btn-primary btn-lg flex-grow-1" data-product-id="<?= (int)$product['id'] ?>" data-product-name="<?= htmlspecialchars($product['name']) ?>">
                        <i class="bi bi-cart-plus me-2"></i>Add to Cart
                    </button>
                </div>
                <?php else: ?>
                <div class="mb-4">
                    <a href="login" class="btn btn-outline-primary btn-lg w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login to Order
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Related Products Section -->
<?php if (!empty($relatedProducts)): ?>
<div class="related-products mb-5">
    <h3 class="section-title text-center position-relative pb-3 mb-4">Related Products</h3>
    
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($relatedProducts as $relatedProduct): ?>
            <div class="col">
                <div class="product-card h-100 rounded shadow-sm overflow-hidden border-0" data-product-id="<?= (int)$relatedProduct['id'] ?>">
                    <div class="position-relative">
                        <?php 
                        // Process related product image path
                        $relatedImagePath = $relatedProduct['image_url'] ?? 'images/products/placeholder.jpg';
                        
                        // Remove 'public/' prefix if it exists
                        if (strpos($relatedImagePath, 'public/') === 0) {
                            $relatedImagePath = substr($relatedImagePath, 7);
                        }
                        
                        // Remove leading slash if present
                        if (strpos($relatedImagePath, '/') === 0) {
                            $relatedImagePath = substr($relatedImagePath, 1);
                        }
                        ?>
                        <a href="product?id=<?= (int)$relatedProduct['id'] ?>" class="text-decoration-none">
                            <img src="<?= htmlspecialchars($relatedImagePath) ?>" 
                                 class="card-img-top product-img" 
                                 alt="<?= htmlspecialchars($relatedProduct['name']) ?>" 
                                 onerror="this.src='images/products/placeholder.jpg'" 
                                 style="height: 200px; object-fit: cover;">
                            <div class="product-overlay position-absolute top-0 end-0 m-2">
                                <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm">$<?= number_format((float)$relatedProduct['price'], 2) ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="card-body d-flex flex-column p-3">
                        <h5 class="card-title fw-bold mb-3">
                            <a href="product?id=<?= (int)$relatedProduct['id'] ?>" class="text-decoration-none text-dark">
                                <?= htmlspecialchars($relatedProduct['name']) ?>
                            </a>
                        </h5>
                        <div class="mt-auto text-center">
                            <a href="product?id=<?= (int)$relatedProduct['id'] ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-eye me-2"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Back to Category Button -->
<div class="text-center mb-5">
    <a href="products<?= isset($product['category']) ? '?category=' . urlencode($product['category']) : '' ?>" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left me-2"></i>Back to <?= isset($product['category']) ? htmlspecialchars($product['category']) : 'Products' ?>
    </a>
</div>

<!-- Success Modal for Add to Cart -->
<?php if (isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="cartModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cart-check me-2"></i>Item Added to Cart
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="text-success mb-3">
                        <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-bold" id="modal-product-name"><?= htmlspecialchars($product['name']) ?></h5>
                    <p class="text-muted">
                        <span id="modal-quantity">1</span> item(s) have been added to your cart
                    </p>
                    <div class="alert alert-light border mt-3">
                        <div class="d-flex justify-content-between">
                            <span>Total items in cart:</span>
                            <span class="fw-bold" id="modal-cart-count">0</span>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span>Cart total:</span>
                            <span class="fw-bold" id="modal-cart-total">$0.00</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-arrow-left me-1"></i>Continue Shopping
                </button>
                <a href="cart" class="btn btn-primary">
                    <i class="bi bi-cart me-1"></i>View Cart
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle quantity changes
    const quantityInput = document.getElementById('quantity-input');
    const increaseBtn = document.querySelector('.quantity-increase');
    const decreaseBtn = document.querySelector('.quantity-decrease');
    
    function validateQuantity() {
        let value = parseInt(quantityInput.value);
        if (isNaN(value) || value < 1) {
            quantityInput.value = 1;
        } else if (value > 99) {
            quantityInput.value = 99;
        }
    }
    
    increaseBtn.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value);
        if (currentValue < 99) {
            quantityInput.value = currentValue + 1;
        }
    });
    
    decreaseBtn.addEventListener('click', function() {
        let currentValue = parseInt(quantityInput.value);
        if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
        }
    });
    
    quantityInput.addEventListener('change', validateQuantity);
    
    // Handle add to cart
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    const modalProductName = document.getElementById('modal-product-name');
    const modalQuantity = document.getElementById('modal-quantity');
    const modalCartCount = document.getElementById('modal-cart-count');
    const modalCartTotal = document.getElementById('modal-cart-total');
    const cartModal = document.getElementById('cartModal');
    const bsModal = new bootstrap.Modal(cartModal);
    
    addToCartBtn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const quantity = quantityInput.value;
        const name = this.dataset.productName;
        
        console.log(`Adding to cart: ${name} (ID: ${productId}), Quantity: ${quantity}`);
        
        // Add to cart via AJAX
        fetch(`cart/add-to-cart.php?product_id=${productId}&quantity=${quantity}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Add to cart response:', data);
            
            // Show the success modal
            modalProductName.textContent = name;
            modalQuantity.textContent = quantity;
            
            // Get cart data from session via a new endpoint
            fetch('cart/info.php?t=' + new Date().getTime(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Cart data received:', data); // Debug output
                if (data && typeof data === 'object') {
                    modalCartCount.textContent = data.itemCount || '0';
                    modalCartTotal.textContent = '$' + (parseFloat(data.total) || 0).toFixed(2);
                } else {
                    console.error('Invalid data format received:', data);
                    modalCartCount.textContent = '0';
                    modalCartTotal.textContent = '$0.00';
                }
                
                // Hide the modal if it's already showing and then show it again
                if (bsModal._isShown) {
                    bsModal.hide();
                    setTimeout(() => { bsModal.show(); }, 150);
                } else {
                    bsModal.show();
                }
            })
            .catch(error => {
                console.error('Error fetching cart info:', error);
                // Still show the modal, but with default values
                modalCartCount.textContent = '0';
                modalCartTotal.textContent = '$0.00';
                bsModal.show();
            });
        })
        .catch(error => {
            console.error('Error adding to cart:', error);
            alert('There was an error adding this item to your cart. Please try again.');
        });
    });
});
</script> 