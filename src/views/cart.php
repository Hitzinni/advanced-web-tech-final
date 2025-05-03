<?php
// This view uses the main layout.php template 
// The content below will be integrated into the main site layout with proper navigation

$itemCount = count($cartItems);

// Generate base URL for cart actions
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$cartBase = "{$protocol}://{$host}";

if (empty($cartItems)) {
    $content = <<<HTML
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
                    <a href="products" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-bag-plus me-2"></i>Browse Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
HTML;
} else {
    $content = <<<HTML
<!-- Cart Items -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow border-0 overflow-hidden mb-4">
            <div class="card-header bg-light py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">
                        <i class="bi bi-cart3 me-2"></i>Cart Items (<span id="cart-item-count">{$itemCount}</span>)
                    </h2>
                    <a href="/cart/clear.php" class="btn btn-sm btn-outline-danger">
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
HTML;

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
        $removeUrl = "/cart/remove.php?product_id={$itemId}";

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

        $iconClass = match ($category) {
            'Fruits' => 'apple',
            'Vegetables' => 'flower3',
            'Bakery' => 'bread-slice',
            'Dairy' => 'egg-fried',
            'Beverages' => 'cup-hot',
            default => 'box'
        };

        $content .= <<<HTML
<tr class="cart-item" data-item-id="{$itemId}">
    <td>
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <div class="bg-light rounded text-center p-2" style="width: 60px; height: 60px;">
                    <i class="bi bi-{$iconClass} text-primary" style="font-size: 1.5rem; line-height: 40px;"></i>
                </div>
            </div>
            <div>
                <h6 class="mb-1">{$itemName}</h6>
                <span class="badge bg-info text-white">{$categoryDisplay}</span>
            </div>
        </div>
    </td>
    <td class="text-center align-middle">\${$formattedPrice}</td>
    <td class="text-center align-middle">
        <div class="d-inline-block">
            <form action="/cart/update.php" method="POST" class="d-flex align-items-center">
                <input type="hidden" name="csrf_token" value="{$csrfToken}">
                <input type="hidden" name="product_id" value="{$itemId}">
                <input type="number" name="quantity" value="{$itemQuantity}" min="1" max="99" 
                       class="form-control form-control-sm text-center" style="width: 60px;">
                <button type="submit" class="btn btn-sm btn-outline-secondary ms-2">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
            </form>
        </div>
    </td>
    <td class="text-center align-middle item-total">
        \${$formattedTotal}
    </td>
    <td class="text-center align-middle">
        <a href="{$removeUrl}" class="btn btn-sm btn-danger">
            <i class="bi bi-trash"></i>
        </a>
    </td>
</tr>
HTML;
    }

    $formattedCartTotal = number_format($cartTotal, 2);

    $content .= <<<HTML
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between">
            <a href="products" class="btn btn-outline-primary">
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
                    <span class="fw-bold" id="cart-subtotal">\${$formattedCartTotal}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping:</span>
                    <span class="fw-bold text-success">Free</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <span class="h5">Total:</span>
                    <span class="h5 fw-bold" id="cart-total">\${$formattedCartTotal}</span>
                </div>
                <a href="checkout" class="btn btn-success w-100 btn-lg">
                    <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                </a>
            </div>
        </div>
    </div>
</div>
HTML;
}

$content .= <<<HTML
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cart page loaded');
});
</script>
HTML;

return $content;
?>