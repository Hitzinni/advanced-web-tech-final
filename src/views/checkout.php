<?php
// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash_message'] = [
        'type' => 'warning',
        'text' => 'Please login to proceed with checkout.'
    ];
    header('Location: login');
    exit;
}

// Ensure cart is not empty
if (!isset($_SESSION['cart']) || !isset($_SESSION['cart']['items']) || empty($_SESSION['cart']['items'])) {
    $_SESSION['flash_message'] = [
        'type' => 'warning',
        'text' => 'Your cart is empty. Please add products before checkout.'
    ];
    header('Location: cart');
    exit;
}

// Calculate subtotal and apply shipping fee
$cartItems = $_SESSION['cart']['items'];
$subtotal = 0;

foreach ($cartItems as $item) {
    $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
    $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
    $subtotal += $itemPrice * $itemQuantity;
}

// Apply shipping fee logic - $5 shipping fee for orders under $25
$shippingFee = ($subtotal < 25) ? 5.00 : 0.00;
$total = $subtotal + $shippingFee;

// Store calculated values in session
$_SESSION['cart']['subtotal'] = $subtotal;
$_SESSION['cart']['shipping'] = $shippingFee;
$_SESSION['cart']['total'] = $total;

// Format currency values
$formattedSubtotal = number_format($subtotal, 2);
$formattedShipping = number_format($shippingFee, 2);
$formattedTotal = number_format($total, 2);

// Create CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get user address and data from controller variables if available
$userAddress = isset($userAddress) ? $userAddress : null;
$userData = isset($userData) ? $userData : null;
?>

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
                    <li class="breadcrumb-item"><a href="cart" class="text-white">Cart</a></li>
                    <li class="breadcrumb-item active text-white-50" aria-current="page">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row">
    <!-- Checkout Form -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow border-0 overflow-hidden">
            <div class="card-header bg-light py-3">
                <h2 class="h5 mb-0"><i class="bi bi-person-lines-fill me-2"></i>Shipping Information</h2>
                <?php if ($userAddress): ?>
                <small class="text-success"><i class="bi bi-check-circle me-1"></i>Using your saved address</small>
                <?php endif; ?>
            </div>
            <div class="card-body p-4">
                <form action="process-checkout" method="POST" id="checkout-form">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name*</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?= $userAddress['first_name'] ?? (isset($userData['name']) ? explode(' ', $userData['name'])[0] : '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name*</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?= $userAddress['last_name'] ?? (isset($userData['name']) ? (strpos($userData['name'], ' ') !== false ? substr($userData['name'], strpos($userData['name'], ' ') + 1) : '') : '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">Email Address*</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= $userData['email'] ?? '' ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="phone" class="form-label">Phone Number*</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= $userAddress['phone'] ?? $userData['phone'] ?? '' ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Street Address*</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?= $userAddress['address'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label">City*</label>
                            <input type="text" class="form-control" id="city" name="city" 
                                   value="<?= $userAddress['city'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="state" class="form-label">County*</label>
                            <input type="text" class="form-control" id="state" name="state" 
                                   value="<?= $userAddress['state'] ?? '' ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="zip" class="form-label">Post Code*</label>
                            <input type="text" class="form-control" id="zip" name="zip" 
                                   value="<?= $userAddress['zip'] ?? '' ?>" required>
                        </div>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="save_address" name="save_address" value="1" <?= $userAddress ? '' : 'checked' ?>>
                                <label class="form-check-label" for="save_address">
                                    Save this address for future orders
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h3 class="h5 mb-3"><i class="bi bi-credit-card me-2"></i>Payment Method</h3>
                    
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="form-check mb-3">
                                <input class="form-check-input payment-method" type="radio" name="payment_method" id="payment_cod" value="cod" checked>
                                <label class="form-check-label" for="payment_cod">
                                    <i class="bi bi-cash me-2"></i>Cash on Delivery
                                </label>
                                <div class="form-text">Pay when your order is delivered.</div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input payment-method" type="radio" name="payment_method" id="payment_card" value="card">
                                <label class="form-check-label" for="payment_card">
                                    <i class="bi bi-credit-card me-2"></i>Credit Card
                                </label>
                                <div class="form-text">Pay now with your credit/debit card.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div id="card-payment-section" style="display: none;">
                        <h3 class="h5 mb-3"><i class="bi bi-credit-card me-2"></i>Card Information</h3>
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="card_name" class="form-label">Name on Card*</label>
                                <input type="text" class="form-control card-field" id="card_name" name="card_name">
                            </div>
                            <div class="col-12">
                                <label for="card_number" class="form-label">Card Number*</label>
                                <input type="text" class="form-control card-field" id="card_number" name="card_number"
                                       pattern="[0-9]{16}" placeholder="1234567890123456">
                                <small class="text-muted">16 digits with no spaces or dashes</small>
                            </div>
                            <div class="col-md-6">
                                <label for="exp_date" class="form-label">Expiration Date*</label>
                                <input type="text" class="form-control card-field" id="exp_date" name="exp_date"
                                       pattern="(0[1-9]|1[0-2])\/[0-9]{2}" placeholder="MM/YY">
                            </div>
                            <div class="col-md-6">
                                <label for="cvv" class="form-label">CVV*</label>
                                <input type="text" class="form-control card-field" id="cvv" name="cvv"
                                       pattern="[0-9]{3,4}" placeholder="123">
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" class="text-decoration-none">Terms and Conditions</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-lock-fill me-2"></i>Complete Order
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Order Summary -->
    <div class="col-lg-4">
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-light py-3">
                <h2 class="h5 mb-0">
                    <i class="bi bi-receipt me-2"></i>Order Summary
                </h2>
            </div>
            <div class="card-body">
                <!-- Cart Items Summary -->
                <div class="cart-items-summary mb-3">
                    <h3 class="h6 fw-bold mb-3">Items in Cart (<?= count($cartItems) ?>)</h3>
                    <?php foreach ($cartItems as $item): ?>
                        <?php 
                        $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
                        $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
                        $itemTotal = $itemPrice * $itemQuantity;
                        ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <div class="fw-medium"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="small text-muted">Qty: <?= $itemQuantity ?> × $<?= number_format($itemPrice, 2) ?></div>
                            </div>
                            <div class="fw-bold">$<?= number_format($itemTotal, 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Cost Summary -->
                <div class="cost-summary mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span class="fw-bold">$<?= $formattedSubtotal ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <?php if ($shippingFee > 0): ?>
                            <span class="fw-bold">$<?= $formattedShipping ?></span>
                        <?php else: ?>
                            <span class="fw-bold text-success">Free</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($shippingFee > 0): ?>
                    <div class="alert alert-info py-2 px-3 mb-2 small">
                        <i class="bi bi-info-circle me-1"></i>
                        Add $<?= number_format(25 - $subtotal, 2) ?> more to qualify for free shipping!
                    </div>
                    <?php else: ?>
                    <div class="alert alert-success py-2 px-3 mb-2 small">
                        <i class="bi bi-check-circle me-1"></i>
                        You've qualified for free shipping!
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h5">Total:</span>
                        <span class="h5 fw-bold">$<?= $formattedTotal ?></span>
                    </div>
                </div>
                
                <a href="cart" class="btn btn-outline-primary btn-sm w-100 mb-2">
                    <i class="bi bi-arrow-left me-2"></i>Back to Cart
                </a>
            </div>
        </div>
        
        <div class="card shadow border-0 mb-4">
            <div class="card-body">
                <h3 class="h6 fw-bold mb-3"><i class="bi bi-shield-check me-2"></i>Secure Checkout</h3>
                <p class="small text-muted mb-3">
                    Your payment information is processed securely. We do not store credit card details nor have access to your credit card information.
                </p>
                <div class="payment-icons text-center">
                    <i class="bi bi-credit-card me-2 fs-4"></i>
                    <i class="bi bi-credit-card-2-front me-2 fs-4"></i>
                    <i class="bi bi-credit-card-2-back me-2 fs-4"></i>
                    <i class="bi bi-lock fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment method toggle
    const paymentMethods = document.querySelectorAll('.payment-method');
    const cardSection = document.getElementById('card-payment-section');
    const cardFields = document.querySelectorAll('.card-field');
    
    // Function to toggle required attribute for card fields
    function toggleCardFields(isCardPayment) {
        cardSection.style.display = isCardPayment ? 'block' : 'none';
        
        // Toggle required attribute on card fields
        cardFields.forEach(field => {
            field.required = isCardPayment;
        });
    }
    
    // Initial setup based on default selection
    toggleCardFields(document.getElementById('payment_card').checked);
    
    // Add event listeners to payment method radios
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            toggleCardFields(document.getElementById('payment_card').checked);
        });
    });
    
    // Form validation
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            // Only validate card details if credit card payment is selected
            if (document.getElementById('payment_card').checked) {
                // Simple card number format validation
                const cardNumber = document.getElementById('card_number').value.replace(/\D/g, '');
                if (cardNumber.length !== 16) {
                    alert('Please enter a valid 16-digit card number');
                    e.preventDefault();
                    return false;
                }
                
                // Simple expiration date validation (MM/YY format)
                const expDate = document.getElementById('exp_date').value;
                if (!/^\d{2}\/\d{2}$/.test(expDate)) {
                    alert('Please enter a valid expiration date in MM/YY format');
                    e.preventDefault();
                    return false;
                }
                
                // Simple CVV validation (3 or 4 digits)
                const cvv = document.getElementById('cvv').value.replace(/\D/g, '');
                if (cvv.length < 3 || cvv.length > 4) {
                    alert('Please enter a valid CVV (3 or 4 digits)');
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
});
</script> 