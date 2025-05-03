<?php
// Start the session
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize cart in session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        'items' => [],
        'total' => 0
    ];
}

// Automatic cart structure repair - more aggressive version
if (isset($_SESSION['cart'])) {
    // Ensure cart structure is valid
    if (!is_array($_SESSION['cart'])) {
        error_log("Critical cart error: Cart is not an array. Resetting cart.");
        $_SESSION['cart'] = [
            'items' => [],
            'total' => 0
        ];
    }
    
    // Check if items is an array
    if (!isset($_SESSION['cart']['items']) || !is_array($_SESSION['cart']['items'])) {
        error_log("Cart repair: Fixed missing or invalid items array");
        $_SESSION['cart']['items'] = [];
    }
    
    // Check if total exists and is numeric
    if (!isset($_SESSION['cart']['total']) || !is_numeric($_SESSION['cart']['total'])) {
        error_log("Cart repair: Fixed missing or invalid total");
        $_SESSION['cart']['total'] = 0;
    }
    
    // Validate and fix each item in cart
    if (!empty($_SESSION['cart']['items'])) {
        $validItems = [];
        $needsUpdate = false;
        
        foreach ($_SESSION['cart']['items'] as $key => $item) {
            // Skip completely invalid items
            if (!is_array($item)) {
                error_log("Cart repair: Removed invalid item at index {$key} (not an array)");
                $needsUpdate = true;
                continue;
            }
            
            // Create a validated item with required fields
            $validItem = [
                'id' => isset($item['id']) ? (int)$item['id'] : $key + 1,
                'name' => isset($item['name']) ? (string)$item['name'] : 'Unknown Product',
                'price' => isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0,
                'quantity' => isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1,
                'image_url' => isset($item['image_url']) ? (string)$item['image_url'] : 'images/products/placeholder.jpg'
            ];
            
            // Safe category handling - now more robust
            $category = 'Other';
            $categoryDisplay = 'Other';
            
            if (isset($item['category'])) {
                if (is_array($item['category'])) {
                    // Fix: Properly handle array conversion by ensuring we extract a string
                    if (!empty($item['category'])) {
                        $firstCategory = reset($item['category']);
                        $category = is_string($firstCategory) ? $firstCategory : 'Other';
                    } else {
                        $category = 'Other';
                    }
                    $categoryDisplay = htmlspecialchars($category);
                } else {
                    $category = (string)$item['category'];
                    $categoryDisplay = htmlspecialchars($category);
                }
            }
            
            // Ensure category is a string before comparison
            $category = is_array($category) ? 'Other' : (string)$category;
            
            // Add the properly handled category to the validItem
            $validItem['category'] = $category;
            
            $validItems[] = $validItem;
        }
        
        // Replace with validated items
        $_SESSION['cart']['items'] = $validItems;
        
        // Recalculate total if needed
        if ($needsUpdate) {
            $total = 0;
            foreach ($_SESSION['cart']['items'] as $item) {
                $total += (float)$item['price'] * (int)$item['quantity'];
            }
            $_SESSION['cart']['total'] = $total;
            error_log("Cart repair: Recalculated cart total after repairs: {$total}");
        }
    }
}

// Set page title and meta description
$pageTitle = "Your Shopping Cart";
$metaDescription = "View and manage your shopping cart";

// Create cart content based on items in the cart - add safety checks
$cartItems = isset($_SESSION['cart']['items']) && is_array($_SESSION['cart']['items']) ? 
             $_SESSION['cart']['items'] : [];
$cartTotal = isset($_SESSION['cart']['total']) && is_numeric($_SESSION['cart']['total']) ? 
             $_SESSION['cart']['total'] : 0;

// Debug cart structure
error_log("Cart structure after repairs: " . print_r($_SESSION['cart'], true));

$content = <<<HTML
<!-- Cart Page Header Section -->
<div class="category-header position-relative mb-4 bg-primary text-white p-4 rounded shadow">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h1 class="fw-bold mb-2"><i class="bi bi-cart4 me-2"></i>Your Shopping Cart</h1>
            <p class="lead mb-0">View and manage your items</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="../" class="text-white">Home</a></li>
                    <li class="breadcrumb-item"><a href="../products" class="text-white">Products</a></li>
                    <li class="breadcrumb-item active text-white-50" aria-current="page">Cart</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
HTML;

// If cart is empty, show empty cart message
if (empty($cartItems)) {
    $content .= <<<HTML
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
                    <a href="../products" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-bag-plus me-2"></i>Browse Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
HTML;
} else {
    // Show cart items if cart is not empty
    $itemCount = is_array($cartItems) ? count($cartItems) : 0; // Calculate count here
    $content .= <<<HTML
<!-- Cart Items -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow border-0 overflow-hidden mb-4">
            <div class="card-header bg-light py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">
                        <i class="bi bi-cart3 me-2"></i>Cart Items (<span id="cart-item-count">{$itemCount}</span>)
                    </h2>
                    <a href="../cart/clear.php" class="btn btn-sm btn-outline-danger">
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

    // Process each cart item individually with careful error handling
    foreach ($cartItems as $item) {
        // Safe getters for all item properties with defaults
        $itemId = isset($item['id']) ? (int)$item['id'] : 0;
        $itemName = isset($item['name']) ? htmlspecialchars((string)$item['name']) : 'Unknown Product';
        $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
        $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
        $itemTotal = $itemPrice * $itemQuantity;
        
        // Format prices before the HEREDOC
        $formattedPrice = number_format($itemPrice, 2);
        $formattedTotal = number_format($itemTotal, 2);
        
        // Safe category handling - now more robust
        $category = 'Other';
        $categoryDisplay = 'Other';
        
        if (isset($item['category'])) {
            if (is_array($item['category'])) {
                // Fix: Properly handle array conversion by ensuring we extract a string
                if (!empty($item['category'])) {
                    $firstCategory = reset($item['category']);
                    $category = is_string($firstCategory) ? $firstCategory : 'Other';
                } else {
                    $category = 'Other';
                }
                $categoryDisplay = htmlspecialchars($category);
            } else {
                $category = (string)$item['category'];
                $categoryDisplay = htmlspecialchars($category);
            }
        }
        
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
        
        // Ensure category is a string before comparison
        $category = is_array($category) ? 'Other' : (string)$category;
        
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
        
        $content .= <<<HTML
<tr class="cart-item" data-item-id="{$itemId}">
    <td>
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-3">
                <div class="bg-light rounded overflow-hidden" style="width: 60px; height: 60px;">
                    <img src="../{$imagePath}" alt="{$itemName}" class="w-100 h-100" style="object-fit: cover;" 
                         onerror="this.onerror=null; this.src='../images/products/placeholder.jpg'; this.parentNode.innerHTML='<div class=\'text-center p-2\'><i class=\'bi bi-{$iconClass} text-primary\' style=\'font-size: 1.5rem; line-height: 40px;\'></i></div>'">
                </div>
            </div>
            <div>
                <h6 class="mb-1">{$itemName}</h6>
                <span class="badge bg-info text-white">{$categoryDisplay}</span>
            </div>
        </div>
    </td>
    <td class="text-center align-middle">$ {$formattedPrice}</td>
    <td class="text-center align-middle">
        <div class="d-inline-block">
            <form action="../cart/update.php" method="POST" class="d-flex align-items-center quantity-form" data-product-id="{$itemId}" data-product-name="{$itemName}">
                <input type="hidden" name="product_id" value="{$itemId}">
                <input type="number" name="quantity" value="{$itemQuantity}" min="1" max="99" 
                       class="form-control form-control-sm text-center quantity-input" style="width: 60px;">
                <button type="submit" class="btn btn-sm btn-outline-secondary ms-2 update-quantity-btn">
                    <i class="bi bi-arrow-repeat"></i>
                </button>
            </form>
        </div>
    </td>
    <td class="text-center align-middle item-total">
        $ {$formattedTotal}
    </td>
    <td class="text-center align-middle">
        <a href="../cart/remove.php?product_id={$itemId}" onclick="removeItem({$itemId}); return false;" class="btn btn-sm btn-danger">
            <i class="bi bi-trash"></i>
        </a>
    </td>
</tr>
HTML;
    }

    // Calculate formatted total before the HEREDOC
    $formattedCartTotal = number_format($cartTotal, 2);
    
    // Determine shipping fee and total with shipping
    $shippingFee = ($cartTotal < 25) ? 5.00 : 0.00;
    $totalWithShipping = $cartTotal + $shippingFee;
    $formattedTotalWithShipping = number_format($totalWithShipping, 2);
    $formattedRemainingForFreeShipping = number_format(25 - $cartTotal, 2);
    
    // Build shipping message based on cart total
    $shippingDisplay = '';
    if ($cartTotal < 25) {
        $shippingDisplay = '<span class="fw-bold">$5.00</span>';
        $shippingAlertDisplay = '<div class="alert alert-info py-2 px-3 mb-2 small">
            <i class="bi bi-info-circle me-1"></i>
            Add $' . $formattedRemainingForFreeShipping . ' more to qualify for free shipping!
        </div>';
    } else {
        $shippingDisplay = '<span class="fw-bold text-success">Free</span>';
        $shippingAlertDisplay = '';
    }

    $content .= <<<HTML
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between">
            <a href="../products" class="btn btn-outline-primary">
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
                    <span class="fw-bold" id="cart-subtotal">$ {$formattedCartTotal}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Shipping:</span>
                    {$shippingDisplay}
                </div>
                {$shippingAlertDisplay}
                <hr>
                <div class="d-flex justify-content-between mb-4">
                    <span class="h5">Total:</span>
                    <span class="h5 fw-bold" id="cart-total">$ {$formattedTotalWithShipping}</span>
                </div>
                <a href="../checkout" class="btn btn-success w-100 btn-lg" id="checkout-btn">
                    <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                </a>
            </div>
        </div>
    </div>
</div>
HTML;
}

// Include the layout
require_once '../../src/views/layout.php'; 

?>

<script>
function removeItem(productId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        // Create a form dynamically
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../cart/remove.php';
        
        // Create hidden input for product_id
        const productInput = document.createElement('input');
        productInput.type = 'hidden';
        productInput.name = 'product_id';
        productInput.value = productId;
        form.appendChild(productInput);
        
        // Submit the form
        document.body.appendChild(form);
        form.submit();
    }
}

// Add event listeners to all quantity forms once the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get all quantity forms
    const quantityForms = document.querySelectorAll('.quantity-form');
    
    // Add event listeners to each form
    quantityForms.forEach(form => {
        const productId = form.getAttribute('data-product-id');
        const productName = form.getAttribute('data-product-name');
        const quantityInput = form.querySelector('.quantity-input');
        
        // Validate input on change
        quantityInput.addEventListener('change', function() {
            let value = parseInt(this.value, 10);
            
            // Ensure it's a valid number
            if (isNaN(value) || value < 1) {
                this.value = 1;
            } else if (value > 99) {
                this.value = 99;
            }
        });
        
        // Handle form submission
        form.addEventListener('submit', function(e) {
            // Additional validation before submit
            const quantity = parseInt(quantityInput.value, 10);
            if (isNaN(quantity) || quantity < 1 || quantity > 99) {
                e.preventDefault();
                alert('Please enter a valid quantity between 1 and 99.');
                return false;
            }
            
            // Log for debugging
            console.log(`Updating ${productName} (ID: ${productId}) to quantity: ${quantity}`);
        });
    });
});
</script> 