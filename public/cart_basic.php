<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Display HTML header
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Basic Cart Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1>Shopping Cart (Basic View)</h1>
        <div class="alert alert-info">This is a simplified cart view that bypasses the templating system</div>';

// Check if cart exists in session
if (!isset($_SESSION['cart']) || empty($_SESSION['cart']['items'])) {
    echo '<div class="alert alert-warning">Your cart is empty.</div>';
    
    // Create a sample cart for testing
    $_SESSION['cart'] = [
        'items' => [
            [
                'id' => 1,
                'name' => 'Test Product',
                'price' => 19.99,
                'quantity' => 2,
                'category' => 'Test'
            ]
        ],
        'total' => 39.98
    ];
    
    echo '<div class="alert alert-success">Created a sample cart for testing.</div>';
}

// Display cart contents
$cartItems = $_SESSION['cart']['items'];
$cartTotal = $_SESSION['cart']['total'];

echo '<h2>Cart Contents</h2>';
echo '<div class="card mb-4">
    <div class="card-header">
        <h3 class="h5 mb-0">Items (' . count($cartItems) . ')</h3>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>';

foreach ($cartItems as $item) {
    $itemId = isset($item['id']) ? (int)$item['id'] : 0;
    $itemName = isset($item['name']) ? htmlspecialchars((string)$item['name']) : 'Unknown Product';
    $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
    $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
    $itemTotal = $itemPrice * $itemQuantity;
    
    echo '<tr>
        <td>' . $itemName . '</td>
        <td>$' . number_format($itemPrice, 2) . '</td>
        <td>' . $itemQuantity . '</td>
        <td>$' . number_format($itemTotal, 2) . '</td>
    </tr>';
}

echo '</tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total:</th>
                <th>$' . number_format($cartTotal, 2) . '</th>
            </tr>
        </tfoot>
    </table>
    </div>
</div>';

// Add debug information
echo '<h2>Debug Information</h2>';
echo '<div class="card mb-4">
    <div class="card-header">
        <h3 class="h5 mb-0">Server Information</h3>
    </div>
    <div class="card-body">
        <pre>';
echo 'PHP Version: ' . phpversion() . "\n";
echo 'Server Software: ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo 'Document Root: ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo 'Script Filename: ' . ($_SERVER['SCRIPT_FILENAME'] ?? 'Unknown') . "\n";
echo '</pre>
    </div>
</div>';

// Navigation links
echo '<div class="mt-4">
    <a href="index.php" class="btn btn-primary me-2">Home</a>
    <a href="cart_direct.php" class="btn btn-success me-2">Use Direct Cart Page</a>
</div>';

// Close HTML
echo '</div>
</body>
</html>';
?> 