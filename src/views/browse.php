<!-- Page Header Section -->
<div class="category-header position-relative mb-4 bg-primary text-white p-4 rounded shadow">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h1 class="fw-bold mb-2">Browse Products</h1>
            <p class="lead mb-0">Discover fresh, high-quality groceries for your kitchen</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="home" class="text-white">Home</a></li>
                    <li class="breadcrumb-item active text-white-50" aria-current="page">Browse Products</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Featured Categories Section -->
<div class="featured-categories mb-5">
    <div class="row g-3">
        <?php 
        // Define category image mapping (same as elsewhere)
        $categoryImages = [
            'Vegetables' => 'images/vegetables-category.jpg',
            'Fruits' => 'images/fruits-category.jpg',
            'Meat' => 'images/meat-category.jpg',
            'Bakery' => 'images/bakery-category.jpg',
            'Dairy' => 'images/dairy-category.jpg'
        ];
        
        // Category labels and descriptions
        $categoryLabels = [
            'Vegetables' => 'Fresh Vegetables',
            'Fruits' => 'Sweet Fruits',
            'Meat' => 'Quality Meats',
            'Bakery' => 'Fresh Bakery',
            'Dairy' => 'Dairy Products'
        ];
        
        $categoryDesc = [
            'Vegetables' => 'Organic and locally sourced',
            'Fruits' => 'Sweet and juicy options',
            'Meat' => 'Premium cuts and selections',
            'Bakery' => 'Freshly baked daily',
            'Dairy' => 'Fresh milk and cheese'
        ];
        
        foreach ($categories as $category): 
            if (isset($categoryImages[$category])): 
        ?>
        <div class="col-md-4 col-lg-2-4">
            <a href="products?category=<?= urlencode($category) ?>" class="text-decoration-none">
                <div class="category-card position-relative rounded overflow-hidden shadow h-100 <?= $selectedCategory === $category ? 'border border-primary border-3' : '' ?>">
                    <img src="<?= htmlspecialchars($categoryImages[$category]) ?>" class="img-fluid category-img w-100" alt="<?= htmlspecialchars($category) ?>" style="height: 150px; object-fit: cover;">
                    <div class="category-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center p-3 text-white text-center" style="background: rgba(0,0,0,0.5);">
                        <h4 class="h5 fw-bold mb-1"><?= htmlspecialchars($categoryLabels[$category] ?? $category) ?></h4>
                        <p class="small mb-0"><?= htmlspecialchars($categoryDesc[$category] ?? '') ?></p>
                    </div>
                </div>
            </a>
        </div>
        <?php 
            endif; 
        endforeach; 
        ?>
    </div>
</div>

<!-- Category Filter Section -->
<div class="filter-section mb-4 p-4 bg-white rounded shadow-sm">
    <?php 
    // Define category icons for Bootstrap Icons
    $categoryIcons = [
        'Vegetables' => 'bi-tree',
        'Fruits' => 'bi-apple',
        'Meat' => 'bi-egg-fried',
        'Bakery' => 'bi-bread-slice',
        'Dairy' => 'bi-cup-hot'
    ];
    ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="category-select" class="form-label fw-medium">Category</label>
            <div class="input-group">
                <span class="input-group-text bg-primary text-white"><i class="bi bi-grid"></i></span>
                <select id="category-select" class="form-select border-primary">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>" <?= $selectedCategory === $category ? 'selected' : '' ?>>
                            <?= isset($categoryIcons[$category]) ? '<i class="bi '.$categoryIcons[$category].' me-2"></i>' : '' ?><?= htmlspecialchars($category) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Category Thumbnails -->
            <div class="d-flex mt-3 justify-content-center">
                <?php foreach ($categories as $category): ?>
                    <?php if (isset($categoryImages[$category])): ?>
                    <a href="products?category=<?= urlencode($category) ?>" class="mx-2 category-thumbnail-link">
                        <div class="category-thumbnail rounded-circle overflow-hidden <?= $selectedCategory === $category ? 'border border-2 border-primary' : 'border' ?>" 
                             style="width: 50px; height: 50px; transition: transform 0.2s;">
                            <img src="<?= htmlspecialchars($categoryImages[$category]) ?>" 
                                 class="img-fluid w-100 h-100" 
                                 alt="<?= htmlspecialchars($category) ?>" 
                                 style="object-fit: cover;">
                        </div>
                        <div class="text-center small mt-1"><?= htmlspecialchars($category) ?></div>
                    </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="col-md-6">
            <label for="product-select" class="form-label fw-medium">Product</label>
            <div class="input-group">
                <span class="input-group-text bg-primary text-white"><i class="bi bi-basket"></i></span>
                <select id="product-select" class="form-select border-primary" <?= empty($selectedCategory) ? 'disabled' : '' ?>>
                    <option value="">Select Product</option>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= (int)$product['id'] ?>" <?= $selectedProductId === (int)$product['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($product['name']) ?> - $<?= number_format((float)$product['price'], 2) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom 5-column grid */
@media (min-width: 992px) {
    .col-lg-2-4 {
        flex: 0 0 auto;
        width: 20%;
    }
}

.category-thumbnail-link:hover .category-thumbnail {
    transform: scale(1.1);
}

.category-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}
</style>

<!-- Products Display Section -->
<?php if (!empty($selectedCategory)): ?>
    <?php 
    // Define category image mapping (same as below)
    $categoryImages = [
        'Vegetables' => 'images/vegetables-category.jpg',
        'Fruits' => 'images/fruits-category.jpg',
        'Meat' => 'images/meat-category.jpg',
        'Bakery' => 'images/bakery-category.jpg',
        'Dairy' => 'images/dairy-category.jpg'
    ];
    
    // Get the appropriate image for the selected category
    $selectedCategoryImage = $categoryImages[$selectedCategory] ?? 'images/products/placeholder.jpg';
    ?>
    <div class="selected-category mb-4">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="category-thumbnail rounded-circle overflow-hidden border border-primary" style="width: 60px; height: 60px;">
                    <img src="<?= htmlspecialchars($selectedCategoryImage) ?>" class="img-fluid w-100 h-100" alt="<?= htmlspecialchars($selectedCategory) ?>" style="object-fit: cover;">
                </div>
            </div>
            <div class="col">
                <h2 class="h4 fw-bold mb-1"><i class="bi bi-tag-fill text-primary me-2"></i>Category: <?= htmlspecialchars($selectedCategory) ?></h2>
                <p class="text-muted small mb-0">Showing all products in this category</p>
            </div>
        </div>
        <hr class="text-primary mt-3">
    </div>
<?php endif; ?>

<div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
            <div class="col">
                <div class="product-card h-100 rounded shadow-sm overflow-hidden border-0 <?= $selectedProductId === (int)$product['id'] ? 'border-primary border-2' : '' ?>" 
                     data-product-id="<?= (int)$product['id'] ?>">
                    <div class="position-relative">
                        <?php 
                        // Fix image path handling
                        $imagePath = $product['image_url'];
                        
                        // Remove 'public/' prefix if it exists
                        if (strpos($imagePath, 'public/') === 0) {
                            $imagePath = substr($imagePath, 7); // Remove 'public/'
                        }
                        
                        // Remove leading slash if present
                        if (strpos($imagePath, '/') === 0) {
                            $imagePath = substr($imagePath, 1);
                        }
                        ?>
                        <a href="product?id=<?= (int)$product['id'] ?>" class="text-decoration-none product-img-link">
                            <img src="<?= htmlspecialchars($imagePath) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($product['name']) ?>" onerror="this.src='images/products/placeholder.jpg'" style="height: 200px; object-fit: cover;">
                            <div class="product-overlay position-absolute top-0 end-0 m-2">
                                <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm">$<?= number_format((float)$product['price'], 2) ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="card-body d-flex flex-column p-3">
                        <h5 class="card-title fw-bold mb-3">
                            <a href="product?id=<?= (int)$product['id'] ?>" class="text-decoration-none text-dark">
                                <?= htmlspecialchars($product['name']) ?>
                            </a>
                        </h5>
                        <div class="mb-3 text-primary">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-half"></i>
                            <small class="text-muted ms-1">(4.5)</small>
                        </div>
                        <div class="mt-auto text-center">
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <div class="d-flex align-items-center justify-content-center mb-2">
                                    <div class="input-group" style="max-width: 160px;">
                                        <button type="button" class="btn btn-outline-secondary quantity-decrease">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number" class="form-control text-center quantity-input" value="1" min="1" max="99" style="min-width: 60px; width: 60px;">
                                        <button type="button" class="btn btn-outline-secondary quantity-increase">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <button class="btn btn-primary w-100 order-btn" data-product-id="<?= (int)$product['id'] ?>">
                                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                                </button>
                            <?php else: ?>
                                <a href="login" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Login to Order
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php elseif (!empty($selectedCategory)): ?>
        <div class="col-12">
            <div class="alert alert-info bg-white border-info d-flex align-items-center p-4 shadow-sm">
                <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                <div>No products found in this category. Try selecting a different category.</div>
            </div>
        </div>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info bg-white border-primary d-flex align-items-center p-4 shadow-sm">
                <i class="bi bi-arrow-up-circle-fill text-primary fs-4 me-3"></i>
                <div>Please select a category above to view available products.</div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($selectedCategory) && isset($_SESSION['user_id'])): ?>
<!-- Order Success Modal -->
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
                    <h5 class="fw-bold" id="modal-product-name"></h5>
                    <p class="text-muted">
                        <span id="modal-quantity"></span> item(s) have been added to your cart
                    </p>
                    <div class="alert alert-light border mt-3">
                        <div class="d-flex justify-content-between">
                            <span>Total items in cart:</span>
                            <span class="fw-bold" id="modal-cart-count"></span>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span>Cart total:</span>
                            <span class="fw-bold" id="modal-cart-total"></span>
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

<!-- Related Categories Section -->
<?php if (!empty($categories) && count($categories) > 1): ?>
<div class="related-categories mb-5">
    <h3 class="section-title text-center position-relative pb-3 mb-4">Explore Other Categories</h3>
    
    <div class="row g-3">
        <?php 
        // Define category image mapping
        $categoryImages = [
            'Vegetables' => 'images/vegetables-category.jpg',
            'Fruits' => 'images/fruits-category.jpg',
            'Meat' => 'images/meat-category.jpg',
            'Bakery' => 'images/bakery-category.jpg',
            'Dairy' => 'images/dairy-category.jpg'
        ];
        
        foreach (array_slice($categories, 0, 3) as $category): 
            if ($category !== $selectedCategory): 
            // Get the appropriate image for this category
            $categoryImage = $categoryImages[$category] ?? 'images/products/placeholder.jpg';
        ?>
        <div class="col-md-4">
            <a href="products?category=<?= urlencode($category) ?>" class="text-decoration-none">
                <div class="category-card position-relative rounded overflow-hidden shadow h-100">
                    <img src="<?= htmlspecialchars($categoryImage) ?>" class="img-fluid category-img w-100" alt="<?= htmlspecialchars($category) ?>" style="height: 180px; object-fit: cover;">
                    <div class="category-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-end p-4 text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                        <h4 class="h5 fw-bold mb-2"><?= htmlspecialchars($category) ?></h4>
                        <a href="products?category=<?= urlencode($category) ?>" class="btn btn-sm btn-primary stretched-link">
                            <i class="bi bi-arrow-right-circle me-1"></i>Explore
                        </a>
                    </div>
                </div>
            </a>
        </div>
        <?php 
            endif; 
        endforeach; 
        ?>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category select change handler
    const categorySelect = document.getElementById('category-select');
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            window.location.href = `products?category=${encodeURIComponent(this.value)}`;
        });
    }
    
    // Product select change handler
    const productSelect = document.getElementById('product-select');
    if (productSelect) {
        productSelect.addEventListener('change', function() {
            const category = categorySelect.value;
            window.location.href = `products?category=${encodeURIComponent(category)}&product_id=${this.value}`;
        });
    }
    
    // Product card selection
    const productCards = document.querySelectorAll('.product-card[data-product-id]');
    productCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't navigate if clicking on specific elements
            if (e.target.closest('.order-btn') || 
                e.target.closest('.input-group') || 
                e.target.closest('.product-img-link') ||
                e.target.closest('a')) {
                return;
            }
            
            const productId = this.dataset.productId;
            window.location.href = `product?id=${productId}`;
        });
    });
    
    // Handle quantity buttons
    document.querySelectorAll('.quantity-decrease').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const input = this.parentElement.querySelector('.quantity-input');
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        });
    });
    
    document.querySelectorAll('.quantity-increase').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const input = this.parentElement.querySelector('.quantity-input');
            const currentValue = parseInt(input.value);
            if (currentValue < 99) {
                input.value = currentValue + 1;
            }
        });
    });
    
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        input.addEventListener('change', function() {
            if (this.value < 1) this.value = 1;
            if (this.value > 99) this.value = 99;
        });
    });
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Order button handlers - now using the cart system
    const orderBtns = document.querySelectorAll('.order-btn');
    const cartModal = document.getElementById('cartModal');
    
    if (orderBtns.length) {
        const modalProductName = document.getElementById('modal-product-name');
        const modalQuantity = document.getElementById('modal-quantity');
        const modalCartCount = document.getElementById('modal-cart-count');
        const modalCartTotal = document.getElementById('modal-cart-total');
        const bsModal = cartModal ? new bootstrap.Modal(cartModal) : null;
        
        orderBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent card click
                
                const productId = this.dataset.productId;
                const card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
                const name = card.querySelector('.card-title').textContent;
                const quantityInput = card.querySelector('.quantity-input');
                const quantity = quantityInput ? parseInt(quantityInput.value) : 1;
                
                // Use AJAX to add to cart
                fetch(`cart/add-to-cart.php?product_id=${productId}&quantity=${quantity}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Always show the modal for any successful addition
                    if (bsModal && modalProductName) {
                        modalProductName.textContent = name;
                        modalQuantity.textContent = quantity;
                        
                        // Get cart data from session via a new endpoint
                        fetch('/cart/info.php?t=' + new Date().getTime(), {
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
                    } else {
                        // Fallback if no modal
                        window.location.href = `cart/add-to-cart.php?product_id=${productId}&quantity=${quantity}`;
                    }
                })
                .catch(error => {
                    console.error('Error adding to cart:', error);
                    // Fallback on error
                    window.location.href = `cart/add-to-cart.php?product_id=${productId}&quantity=${quantity}`;
                });
            });
        });
    }
});
</script> 