<?php
// Order not found
if (!isset($order) || empty($order)) {
    $_SESSION['flash_message'] = [
        'type' => 'danger',
        'text' => 'Order details not found.'
    ];
    header('Location: my-orders');
    exit;
}

// Format numbers
$isNewFormat = isset($order['is_new_format']) && $order['is_new_format'];

if ($isNewFormat) {
    $formattedTotal = number_format($order['total_amount'], 2);
    $formattedSubtotal = number_format($order['subtotal'], 2);
    $formattedShipping = number_format($order['shipping_fee'], 2);
    $orderId = $order['id'];
    $orderDate = date('F j, Y g:i A', strtotime($order['ordered_at']));
    $paymentMethod = $order['payment_method'];
    $shippingAddress = $order['shipping_address'];
    $status = ucfirst($order['status']);
} else {
    $formattedTotal = number_format($order['price_at_order'], 2);
    $formattedSubtotal = number_format($order['price_at_order'], 2);
    $formattedShipping = '0.00';
    $orderId = $order['id'];
    $orderDate = date('F j, Y g:i A', strtotime($order['ordered_at']));
    $paymentMethod = 'Credit Card';
    $shippingAddress = 'Not available';
    $status = 'Completed';
}
?>

<!-- Order Receipt Section -->
<div class="category-header position-relative mb-4 bg-primary text-white p-4 rounded shadow">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="fw-bold mb-2"><i class="bi bi-receipt me-2"></i>Order Receipt</h1>
            <p class="lead mb-0">Thank you for your order!</p>
        </div>
        <div class="col-md-6 text-md-end">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-md-end mb-0">
                    <li class="breadcrumb-item"><a href="home" class="text-white">Home</a></li>
                    <li class="breadcrumb-item"><a href="my-orders" class="text-white">My Orders</a></li>
                    <li class="breadcrumb-item active text-white-50" aria-current="page">Receipt</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0"><i class="bi bi-receipt-cutoff me-2"></i>Order #<?= $orderId ?></h2>
            <span class="badge bg-success"><?= $status ?></span>
        </div>
    </div>
    
    <div class="card-body p-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h3 class="h6 fw-bold"><i class="bi bi-info-circle me-2"></i>Order Details</h3>
                <p class="mb-1"><strong>Order ID:</strong> #<?= $orderId ?></p>
                <p class="mb-1"><strong>Date:</strong> <?= $orderDate ?></p>
                <p class="mb-1"><strong>Payment Method:</strong> <?= $paymentMethod ?></p>
                <p class="mb-1"><strong>Status:</strong> <?= $status ?></p>
            </div>
            
            <div class="col-md-6">
                <h3 class="h6 fw-bold"><i class="bi bi-house-door me-2"></i>Shipping Address</h3>
                <address class="mb-0">
                    <?= $shippingAddress ?>
                </address>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 50%">Product</th>
                        <th scope="col" class="text-center">Price</th>
                        <th scope="col" class="text-center">Quantity</th>
                        <th scope="col" class="text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($isNewFormat && isset($order['items'])): ?>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <?php
                                            $imagePath = isset($item['image_url']) ? (string)$item['image_url'] : 'images/products/placeholder.jpg';
                                            
                                            if (strpos($imagePath, 'public/') === 0) {
                                                $imagePath = substr($imagePath, 7);
                                            }
                                            
                                            if (strpos($imagePath, '/') === 0) {
                                                $imagePath = substr($imagePath, 1);
                                            }
                                            
                                            $category = $item['category'] ?? 'Other';
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
                                            <div class="bg-light rounded overflow-hidden" style="width: 50px; height: 50px;">
                                                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="w-100 h-100" style="object-fit: cover;"
                                                     onerror="this.onerror=null; this.src='images/products/placeholder.jpg'; this.parentNode.innerHTML='<div class=\'text-center p-2\'><i class=\'bi bi-<?= $iconClass ?> text-primary\' style=\'font-size: 1.5rem; line-height: 30px;\'></i></div>'">
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($item['product_name']) ?></h6>
                                            <span class="badge bg-info text-white"><?= htmlspecialchars($category) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center align-middle">$<?= number_format($item['price'], 2) ?></td>
                                <td class="text-center align-middle"><?= $item['quantity'] ?></td>
                                <td class="text-center align-middle">
                                    $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Legacy single product order -->
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <?php
                                        $imagePath = isset($order['image_url']) ? (string)$order['image_url'] : 'images/products/placeholder.jpg';
                                        
                                        if (strpos($imagePath, 'public/') === 0) {
                                            $imagePath = substr($imagePath, 7);
                                        }
                                        
                                        if (strpos($imagePath, '/') === 0) {
                                            $imagePath = substr($imagePath, 1);
                                        }
                                        
                                        $category = $order['category'] ?? 'Other';
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
                                        <div class="bg-light rounded overflow-hidden" style="width: 50px; height: 50px;">
                                            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($order['product_name']) ?>" class="w-100 h-100" style="object-fit: cover;"
                                                 onerror="this.onerror=null; this.src='images/products/placeholder.jpg'; this.parentNode.innerHTML='<div class=\'text-center p-2\'><i class=\'bi bi-<?= $iconClass ?> text-primary\' style=\'font-size: 1.5rem; line-height: 30px;\'></i></div>'">
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($order['product_name']) ?></h6>
                                        <span class="badge bg-info text-white"><?= htmlspecialchars($category) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center align-middle">$<?= number_format($order['price_at_order'], 2) ?></td>
                            <td class="text-center align-middle">1</td>
                            <td class="text-center align-middle">
                                $<?= number_format($order['price_at_order'], 2) ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="row">
            <div class="col-md-6 offset-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h3 class="h6 fw-bold mb-3"><i class="bi bi-currency-dollar me-2"></i>Order Summary</h3>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span class="fw-bold">$<?= $formattedSubtotal ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <?php if ($formattedShipping > 0): ?>
                                <span class="fw-bold">$<?= $formattedShipping ?></span>
                            <?php else: ?>
                                <span class="fw-bold text-success">Free</span>
                            <?php endif; ?>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <span class="h6 fw-bold">Total:</span>
                            <span class="h6 fw-bold">$<?= $formattedTotal ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Status Actions - More visible placement -->
    <?php if ($isNewFormat): ?>
        <div class="card bg-light mb-4">
            <div class="card-body">
                <h3 class="h5 mb-3"><i class="bi bi-gear-fill me-2"></i>Order Status Actions</h3>
                
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'manager'): ?>
                    <p>As an administrator, you can update this order to any status:</p>
                    <div class="btn-group w-100 mb-3">
                        <a href="update-order-status?id=<?= $orderId ?>&status=pending" class="btn <?= ($status === 'Pending') ? 'btn-primary' : 'btn-outline-primary' ?>">Pending</a>
                        <a href="update-order-status?id=<?= $orderId ?>&status=processing" class="btn <?= ($status === 'Processing') ? 'btn-primary' : 'btn-outline-primary' ?>">Processing</a>
                        <a href="update-order-status?id=<?= $orderId ?>&status=shipped" class="btn <?= ($status === 'Shipped') ? 'btn-primary' : 'btn-outline-primary' ?>">Shipped</a>
                        <a href="update-order-status?id=<?= $orderId ?>&status=delivered" class="btn <?= ($status === 'Delivered') ? 'btn-primary' : 'btn-outline-primary' ?>">Delivered</a>
                        <a href="update-order-status?id=<?= $orderId ?>&status=cancelled" class="btn <?= ($status === 'Cancelled') ? 'btn-danger' : 'btn-outline-danger' ?>">Cancelled</a>
                    </div>
                <?php elseif ($status === 'Delivered'): ?>
                    <p>Your order has been delivered. Did you receive it?</p>
                    <a href="update-order-status?id=<?= $orderId ?>&status=received" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-check-circle-fill me-2"></i>Yes, I Have Received This Order
                    </a>
                <?php elseif ($status === 'Pending'): ?>
                    <p>Your order is pending processing.</p>
                    <div class="d-grid gap-2">
                        <a href="update-order-status?id=<?= $orderId ?>&status=received" class="btn btn-success mb-2">
                            <i class="bi bi-check-circle-fill me-2"></i>Mark as Received
                        </a>
                        <a href="update-order-status?id=<?= $orderId ?>&status=cancelled" class="btn btn-danger" 
                           onclick="return confirm('Are you sure you want to cancel this order? This action cannot be undone.')">
                            <i class="bi bi-x-circle me-1"></i>Cancel This Order
                        </a>
                        <p class="small text-muted mt-2 mb-0">You can only cancel orders that are still in the "Pending" status.</p>
                    </div>
                <?php elseif ($status !== 'Received'): ?>
                    <p>Current status: <span class="badge bg-primary"><?= $status ?></span></p>
                    <a href="update-order-status?id=<?= $orderId ?>&status=received" class="btn btn-success btn-lg w-100">
                        <i class="bi bi-check-circle-fill me-2"></i>Mark as Received
                    </a>
                <?php else: ?>
                    <p>Current status: <span class="badge bg-success"><?= $status ?></span></p>
                    <p class="mb-0">This order has been marked as received. Thank you for shopping with us!</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="card-footer bg-light py-3">
        <div class="d-flex justify-content-between">
            <a href="my-orders" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-2"></i>Back to My Orders
            </a>
            
            <div class="d-print-none">
                <?php if ($isNewFormat && isset($_SESSION['role']) && $_SESSION['role'] === 'manager'): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" id="orderStatusDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear-fill me-1"></i> Update Status
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="orderStatusDropdown">
                            <li><h6 class="dropdown-header">Change Order Status</h6></li>
                            <li><a class="dropdown-item <?= ($status === 'Pending') ? 'active' : '' ?>" href="update-order-status?id=<?= $orderId ?>&status=pending">Pending</a></li>
                            <li><a class="dropdown-item <?= ($status === 'Processing') ? 'active' : '' ?>" href="update-order-status?id=<?= $orderId ?>&status=processing">Processing</a></li>
                            <li><a class="dropdown-item <?= ($status === 'Shipped') ? 'active' : '' ?>" href="update-order-status?id=<?= $orderId ?>&status=shipped">Shipped</a></li>
                            <li><a class="dropdown-item <?= ($status === 'Delivered') ? 'active' : '' ?>" href="update-order-status?id=<?= $orderId ?>&status=delivered">Delivered</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item <?= ($status === 'Cancelled') ? 'active' : '' ?>" href="update-order-status?id=<?= $orderId ?>&status=cancelled">Cancelled</a></li>
                        </ul>
                    </div>
                <?php elseif ($isNewFormat && $status === 'Delivered'): ?>
                    <a href="update-order-status?id=<?= $orderId ?>&status=received" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Mark as Received
                    </a>
                <?php elseif ($isNewFormat && $status === 'Pending'): ?>
                    <div class="btn-group">
                        <a href="update-order-status?id=<?= $orderId ?>&status=received" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Mark as Received
                        </a>
                        <a href="update-order-status?id=<?= $orderId ?>&status=cancelled" class="btn btn-outline-danger" 
                           onclick="return confirm('Are you sure you want to cancel this order? This action cannot be undone.')">
                            <i class="bi bi-x-circle me-1"></i> Cancel Order
                        </a>
                    </div>
                <?php elseif ($isNewFormat && $status !== 'Received'): ?>
                    <a href="update-order-status?id=<?= $orderId ?>&status=received" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i> Mark as Received
                    </a>
                <?php endif; ?>
            </div>
            
            <button class="btn btn-outline-secondary d-print-none" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>Print Receipt
            </button>
        </div>
    </div>
</div>

<!-- Thank you section -->
<div class="alert alert-success text-center mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>
    <strong>Thank you for your order!</strong> We'll process it right away.
</div>

<!-- Additional information -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h3 class="h5 mb-3"><i class="bi bi-truck me-2"></i>Shipping Information</h3>
                <p>Your order will be processed within 1-2 business days.</p>
                <p>For any questions regarding your order, please contact our customer service.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h3 class="h5 mb-3"><i class="bi bi-question-circle me-2"></i>Need Help?</h3>
                <p>For any questions or concerns about your order, please contact us:</p>
                <p><i class="bi bi-envelope me-2"></i>Email: support@example.com</p>
                <p class="mb-0"><i class="bi bi-telephone me-2"></i>Phone: (123) 456-7890</p>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
<!-- Debug information -->
<div class="card mb-4 border-danger">
    <div class="card-header bg-danger text-white">
        <h3 class="h5 mb-0">Debug Information</h3>
    </div>
    <div class="card-body">
        <p><strong>Is New Format:</strong> <?= $isNewFormat ? 'Yes' : 'No' ?></p>
        <p><strong>Order Status:</strong> <?= $status ?></p>
        <p><strong>User Role:</strong> <?= isset($_SESSION['role']) ? $_SESSION['role'] : 'Not set' ?></p>
        <p><strong>Order User ID:</strong> <?= isset($order['user_id']) ? $order['user_id'] : 'Not set' ?></p>
        <p><strong>Current User ID:</strong> <?= isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set' ?></p>
        <p><strong>Order Structure:</strong> <pre><?php print_r($order); ?></pre></p>
    </div>
</div>
<?php endif; ?> 