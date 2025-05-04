<?php
// This view uses the main layout.php template 
// The content below will be integrated into the main site layout with proper navigation

// Prevent direct access to this template file
if (!defined('BASE_PATH')) {
    header('HTTP/1.0 403 Forbidden');
    echo '<h1>403 Forbidden</h1>';
    echo '<p>You are not allowed to access this file directly.</p>';
    echo '<p><a href="../public/index.php?route=cart">Go to Cart Page</a></p>';
    exit;
}

$itemCount = count($cartItems);

// Use index.php?route= format for all URLs to ensure proper protocol handling
$removeUrlBase = \App\Helpers\View::url('api/cart/remove');
$updateUrlBase = \App\Helpers\View::url('api/cart/update');
$clearCartUrl = \App\Helpers\View::url('api/cart/clear');
$productsUrl = \App\Helpers\View::url('products');
$checkoutUrl = \App\Helpers\View::url('checkout');

// Make sure all API routes use index.php?route= format
$removeUrlBase = str_replace('/api/cart/remove', 'index.php?route=api/cart/remove', $removeUrlBase);
$updateUrlBase = str_replace('/api/cart/update', 'index.php?route=api/cart/update', $updateUrlBase);
$clearCartUrl = str_replace('/api/cart/clear', 'index.php?route=api/cart/clear', $clearCartUrl);

// Debug log all URLs
error_log("Cart URLs - Remove: {$removeUrlBase}, Update: {$updateUrlBase}, Clear: {$clearCartUrl}");

if (empty($cartItems)) {
?>
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
                        <a href="<?= $productsUrl ?>" class="btn btn-primary px-4 py-2">
                            <i class="bi bi-bag-plus me-2"></i>Browse Products
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
} else {
?>
    <!-- Cart Items -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow border-0 overflow-hidden mb-4">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0">
                            <i class="bi bi-cart3 me-2"></i>Cart Items (<span id="cart-item-count"><?= $itemCount ?></span>)
                        </h2>
                        <form action="javascript:void(0);" class="clear-cart-form d-inline">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash me-1"></i>Clear Cart
                            </button>
                        </form>
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
                                    $csrfToken = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';

                                    // Generate the remove URL
                                    $removeUrl = $removeUrlBase . "?product_id={$itemId}";

                                    $category = 'Other';
                                    $categoryDisplay = 'Other';
                                    if (isset($item['category'])) {
                                        if (is_array($item['category'])) {
                                            $category = !empty($item['category']) ? (string)reset($item['category']) : 'Other';
                                        } else {
                                            $category = (string)$item['category'];
                                        }
                                        $categoryDisplay = htmlspecialchars($category);
                                    }

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
                                                    <?php if (!empty($item['image_url'])): ?>
                                                        <?php 
                                                            // Debug original path
                                                            error_log('Original image URL: ' . $item['image_url']);
                                                            
                                                            // Simplified direct path construction
                                                            $fileName = basename($item['image_url']);
                                                            $imageUrl = "images/products/{$fileName}";
                                                            
                                                            // Get the site's base URL without the public part
                                                            $baseUrl = \App\Helpers\View::getBaseUrl();
                                                            
                                                            // Full image URL
                                                            $fullImageUrl = $baseUrl . $imageUrl;
                                                            error_log('Fixed image URL: ' . $fullImageUrl);
                                                        ?>
                                                        <div class="bg-light rounded text-center p-2" style="width: 60px; height: 60px; overflow: hidden;">
                                                            <img src="<?= htmlspecialchars($fullImageUrl) ?>" alt="<?= $itemName ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="bg-light rounded text-center p-2" style="width: 60px; height: 60px;">
                                                            <i class="bi bi-<?= $iconClass ?> text-primary" style="font-size: 1.5rem; line-height: 40px;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1"><?= $itemName ?></h6>
                                                    <span class="badge bg-info text-white"><?= $categoryDisplay ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">$<?= $formattedPrice ?></td>
                                        <td class="text-center align-middle">
                                            <div class="d-inline-block">
                                                <form action="javascript:void(0);" class="update-form d-flex align-items-center">
                                                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                    <input type="hidden" name="product_id" value="<?= $itemId ?>">
                                                    <input type="number" name="quantity" value="<?= $itemQuantity ?>" min="1" max="99"
                                                        class="form-control form-control-sm text-center" style="width: 60px;">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary ms-2" data-product-id="<?= $itemId ?>">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle item-total">
                                            $<?= $formattedTotal ?>
                                        </td>
                                        <td class="text-center align-middle">
                                            <form action="javascript:void(0);" class="remove-form d-inline">
                                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                                <input type="hidden" name="product_id" value="<?= $itemId ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" data-product-id="<?= $itemId ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="<?= $productsUrl ?>" class="btn btn-outline-primary">
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
                    <a href="<?= $checkoutUrl ?>" class="btn btn-success w-100 btn-lg">
                        <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Cart page loaded');
        
        // Handle remove item forms
        const removeItemForms = document.querySelectorAll('.remove-form');
        removeItemForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const productId = form.querySelector('input[name="product_id"]').value;
                const csrf = form.querySelector('input[name="csrf_token"]').value;
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('csrf_token', csrf);
                
                // Use a properly formatted URL that works with the router
                const baseUrl = window.location.origin + window.location.pathname.split('index.php')[0] + 'index.php';
                const apiUrl = baseUrl + '?route=api/cart/remove';
                console.log('Using API URL:', apiUrl);
                
                // Send remove request
                fetch(apiUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    // Try to parse as JSON, but handle cases where it might not be JSON
                    return response.text().then(text => {
                        try {
                            console.log('Response text:', text.substring(0, 100) + '...');  // Log first 100 chars
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                        }
                    });
                })
                .then(data => {
                    if (data.success) {
                        // Remove the row from the table
                        const row = form.closest('tr');
                        row.parentNode.removeChild(row);
                        
                        // Update cart count and total
                        document.getElementById('cart-item-count').textContent = data.itemCount;
                        document.getElementById('cart-subtotal').textContent = '$' + parseFloat(data.cartTotal).toFixed(2);
                        document.getElementById('cart-total').textContent = '$' + parseFloat(data.cartTotal).toFixed(2);
                        
                        // If no items left, reload page to show empty cart message
                        if (data.itemCount === 0) {
                            window.location.reload();
                        }
                        
                        // Show success message
                        alert('Item removed from cart');
                    } else {
                        alert('Failed to remove item: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing the item.');
                });
            });
        });
        
        // Handle update quantity forms
        const updateForms = document.querySelectorAll('.update-form');
        updateForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const productId = form.querySelector('input[name="product_id"]').value;
                const quantity = form.querySelector('input[name="quantity"]').value;
                const csrf = form.querySelector('input[name="csrf_token"]').value;
                
                if (quantity < 1) {
                    alert('Quantity must be at least 1');
                    return;
                }
                
                const formData = new FormData();
                formData.append('product_id', productId);
                formData.append('quantity', quantity);
                formData.append('csrf_token', csrf);
                
                // Use a properly formatted URL that works with the router
                const baseUrl = window.location.origin + window.location.pathname.split('index.php')[0] + 'index.php';
                const apiUrl = baseUrl + '?route=api/cart/update';
                console.log('Using update API URL:', apiUrl);
                
                // Send update request
                fetch(apiUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Update response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    // Try to parse as JSON, but handle cases where it might not be JSON
                    return response.text().then(text => {
                        try {
                            console.log('Update response text:', text.substring(0, 100) + '...');
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                        }
                    });
                })
                .then(data => {
                    if (data.success) {
                        // Update row totals
                        const row = form.closest('tr');
                        const priceCell = row.querySelector('td:nth-child(2)');
                        const totalCell = row.querySelector('td.item-total');
                        
                        if (priceCell && totalCell) {
                            const price = parseFloat(priceCell.textContent.replace('$', ''));
                            const newTotal = price * quantity;
                            totalCell.textContent = '$' + newTotal.toFixed(2);
                        }
                        
                        // Update cart total
                        document.getElementById('cart-subtotal').textContent = '$' + parseFloat(data.cartTotal).toFixed(2);
                        document.getElementById('cart-total').textContent = '$' + parseFloat(data.cartTotal).toFixed(2);
                        
                        // Success - no alert needed
                    } else {
                        alert('Failed to update cart: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the cart.');
                });
            });
        });
        
        // Handle clear cart form
        const clearCartForm = document.querySelector('.clear-cart-form');
        if (clearCartForm) {
            clearCartForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!confirm('Are you sure you want to clear your cart?')) {
                    return;
                }
                
                const csrf = clearCartForm.querySelector('input[name="csrf_token"]').value;
                const formData = new FormData();
                formData.append('csrf_token', csrf);
                
                // Use a properly formatted URL that works with the router
                const baseUrl = window.location.origin + window.location.pathname.split('index.php')[0] + 'index.php';
                const apiUrl = baseUrl + '?route=api/cart/clear';
                console.log('Using clear cart API URL:', apiUrl);
                
                // Send clear cart request
                fetch(apiUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Clear cart response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    // Try to parse as JSON, but handle cases where it might not be JSON
                    return response.text().then(text => {
                        try {
                            console.log('Clear cart response text:', text.substring(0, 100) + '...');
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                        }
                    });
                })
                .then(data => {
                    if (data.success) {
                        // Reload page to show empty cart
                        window.location.reload();
                    } else {
                        alert('Failed to clear cart: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while clearing the cart.');
                });
            });
        }
    });
</script>