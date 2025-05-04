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
                    <li class="breadcrumb-item"><a href="<?= \App\Helpers\View::url('home') ?>" class="text-white">Home</a></li>
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
            'Vegetables' => \App\Helpers\View::asset('images/vegetables-category.jpg'),
            'Fruits' => \App\Helpers\View::asset('images/fruits-category.jpg'),
            'Meat' => \App\Helpers\View::asset('images/meat-category.jpg'),
            'Bakery' => \App\Helpers\View::asset('images/bakery-category.jpg'),
            'Dairy' => \App\Helpers\View::asset('images/dairy-category.jpg')
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
            <a href="<?= \App\Helpers\View::url('products', ['category' => $category]) ?>" class="text-decoration-none">
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
                    <a href="<?= \App\Helpers\View::url('products', ['category' => $category]) ?>" class="mx-2 category-thumbnail-link">
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
        'Vegetables' => \App\Helpers\View::asset('images/vegetables-category.jpg'),
        'Fruits' => \App\Helpers\View::asset('images/fruits-category.jpg'),
        'Meat' => \App\Helpers\View::asset('images/meat-category.jpg'),
        'Bakery' => \App\Helpers\View::asset('images/bakery-category.jpg'),
        'Dairy' => \App\Helpers\View::asset('images/dairy-category.jpg')
    ];
    
    // Get the appropriate image for the selected category
    $selectedCategoryImage = $categoryImages[$selectedCategory] ?? \App\Helpers\View::asset('images/products/placeholder.jpg');
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
                        // Fix image path handling - use absolute URL with View::asset
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
                        <a href="<?= \App\Helpers\View::url('product', ['id' => (int)$product['id']]) ?>" class="text-decoration-none product-img-link">
                            <img src="<?= \App\Helpers\View::asset($imagePath) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($product['name']) ?>" onerror="this.src='<?= \App\Helpers\View::asset('images/products/placeholder.jpg') ?>'" style="height: 200px; object-fit: cover;">
                            <div class="product-overlay position-absolute top-0 end-0 m-2">
                                <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm">$<?= number_format((float)$product['price'], 2) ?></span>
                            </div>
                        </a>
                    </div>
                    <div class="card-body d-flex flex-column p-3">
                        <h5 class="card-title fw-bold mb-3">
                            <a href="<?= \App\Helpers\View::url('product', ['id' => (int)$product['id']]) ?>" class="text-decoration-none text-dark">
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
                                <a href="<?= \App\Helpers\View::url('login') ?>" class="btn btn-outline-primary w-100">
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
            <div class="alert alert-info text-center py-5">
                <i class="bi bi-info-circle fs-1 d-block mb-3"></i>
                <h3 class="h4 mb-3">No Products Found</h3>
                <p class="mb-3">There are currently no products available in this category.</p>
                <a href="<?= \App\Helpers\View::url('products') ?>" class="btn btn-primary">View All Categories</a>
            </div>
        </div>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-primary text-center py-5">
                <i class="bi bi-arrow-up-circle fs-1 d-block mb-3"></i>
                <h3 class="h4 mb-3">Select a Category</h3>
                <p class="mb-3">Please select a category above to browse products.</p>
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
                <a href="<?= \App\Helpers\View::url('cart') ?>" class="btn btn-primary">
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
            'Vegetables' => \App\Helpers\View::asset('images/vegetables-category.jpg'),
            'Fruits' => \App\Helpers\View::asset('images/fruits-category.jpg'),
            'Meat' => \App\Helpers\View::asset('images/meat-category.jpg'),
            'Bakery' => \App\Helpers\View::asset('images/bakery-category.jpg'),
            'Dairy' => \App\Helpers\View::asset('images/dairy-category.jpg')
        ];
        
        foreach (array_slice($categories, 0, 3) as $category): 
            if ($category !== $selectedCategory): 
            // Get the appropriate image for this category
            $categoryImage = $categoryImages[$category] ?? \App\Helpers\View::asset('images/products/placeholder.jpg');
        ?>
        <div class="col-md-4">
            <a href="<?= \App\Helpers\View::url('products', ['category' => $category]) ?>" class="text-decoration-none">
                <div class="category-card position-relative rounded overflow-hidden shadow h-100">
                    <img src="<?= htmlspecialchars($categoryImage) ?>" class="img-fluid category-img w-100" alt="<?= htmlspecialchars($category) ?>" style="height: 180px; object-fit: cover;">
                    <div class="category-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-end p-4 text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                        <h4 class="h5 fw-bold mb-2"><?= htmlspecialchars($category) ?></h4>
                        <a href="<?= \App\Helpers\View::url('products', ['category' => $category]) ?>" class="btn btn-sm btn-primary stretched-link">
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
    const categorySelect = document.getElementById('category-select');
    const productSelect = document.getElementById('product-select');
    
    // Handle category selection change
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const category = this.value;
            if (category) {
                window.location.href = '<?= \App\Helpers\View::url('products') ?>?category=' + encodeURIComponent(category);
            } else {
                window.location.href = '<?= \App\Helpers\View::url('products') ?>';
            }
        });
    }
    
    // Handle product selection change
    if (productSelect) {
        productSelect.addEventListener('change', function() {
            const productId = this.value;
            if (productId) {
                window.location.href = '<?= \App\Helpers\View::url('product') ?>?id=' + encodeURIComponent(productId);
            }
        });
    }
    
    // Quantity adjustment
    const decreaseButtons = document.querySelectorAll('.quantity-decrease');
    const increaseButtons = document.querySelectorAll('.quantity-increase');
    const quantityInputs = document.querySelectorAll('.quantity-input');
    
    decreaseButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('.quantity-input');
            const value = parseInt(input.value, 10);
            if (value > 1) {
                input.value = value - 1;
            }
        });
    });
    
    increaseButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const input = this.parentNode.querySelector('.quantity-input');
            const value = parseInt(input.value, 10);
            if (value < 99) {
                input.value = value + 1;
            }
        });
    });
    
    quantityInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const value = parseInt(this.value, 10);
            if (isNaN(value) || value < 1) {
                this.value = 1;
            } else if (value > 99) {
                this.value = 99;
            }
        });
    });
    
    // Add to cart functionality
    const orderButtons = document.querySelectorAll('.order-btn');
    
    orderButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const quantityInput = this.closest('.product-card').querySelector('.quantity-input');
            const quantity = quantityInput ? parseInt(quantityInput.value, 10) : 1;
            
            // AJAX request to add to cart
            fetch('<?= \App\Helpers\View::url('api/cart/add') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + encodeURIComponent(productId) + 
                      '&quantity=' + encodeURIComponent(quantity) + 
                      '&csrf_token=<?= $_SESSION['csrf_token'] ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Get the product name for the modal
                    const card = button.closest('.product-card');
                    const productName = card.querySelector('.card-title').textContent.trim();
                    
                    // Update modal content
                    const modalProductName = document.getElementById('modal-product-name');
                    const modalQuantity = document.getElementById('modal-quantity');
                    
                    if (modalProductName) {
                        modalProductName.textContent = productName;
                    }
                    
                    if (modalQuantity) {
                        modalQuantity.textContent = quantity;
                    }
                    
                    // Get updated cart data
                    const baseUrl = window.location.origin + window.location.pathname.split('index.php')[0] + 'index.php';
                    const apiUrl = baseUrl + '?route=api/cart/count';
                    console.log('Using cart count API URL:', apiUrl);
                    
                    fetch(apiUrl + '&t=' + new Date().getTime(), {
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
                    .then(cartData => {
                        console.log('Cart data received:', cartData);
                        if (cartData && typeof cartData === 'object') {
                            // Add null checks for DOM elements
                            const modalCartCount = document.getElementById('modal-cart-count');
                            const modalCartTotal = document.getElementById('modal-cart-total');
                            
                            if (modalCartCount) {
                                modalCartCount.textContent = cartData.itemCount || '0';
                            }
                            
                            if (modalCartTotal) {
                                modalCartTotal.textContent = '$' + (parseFloat(cartData.total) || 0).toFixed(2);
                            }
                        } else {
                            console.error('Invalid cart data format received:', cartData);
                            
                            // Add null checks here too
                            const modalCartCount = document.getElementById('modal-cart-count');
                            const modalCartTotal = document.getElementById('modal-cart-total');
                            
                            if (modalCartCount) {
                                modalCartCount.textContent = '0';
                            }
                            
                            if (modalCartTotal) {
                                modalCartTotal.textContent = '$0.00';
                            }
                        }
                        
                        // Show the modal
                        const cartModal = document.getElementById('cartModal');
                        if (cartModal) {
                            const bsModal = new bootstrap.Modal(cartModal);
                            bsModal.show();
                        } else {
                            // If modal doesn't exist, show a simple alert instead
                            alert('Product added to cart successfully!');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching cart info:', error);
                        // Still show the modal with default values
                        const modalCartCount = document.getElementById('modal-cart-count');
                        const modalCartTotal = document.getElementById('modal-cart-total');
                        
                        if (modalCartCount) {
                            modalCartCount.textContent = '0';
                        }
                        
                        if (modalCartTotal) {
                            modalCartTotal.textContent = '$0.00';
                        }
                        
                        const cartModal = document.getElementById('cartModal');
                        if (cartModal) {
                            const bsModal = new bootstrap.Modal(cartModal);
                            bsModal.show();
                        } else {
                            // If modal doesn't exist, show a simple alert instead
                            alert('Product added to cart successfully!');
                        }
                    });
                } else {
                    alert(data.message || 'Failed to add item to cart.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the item to your cart.');
            });
        });
    });
});
</script> 