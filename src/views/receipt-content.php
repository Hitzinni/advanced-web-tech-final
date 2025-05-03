<!-- Order Receipt Header Section -->
<div class="category-header position-relative mb-4 bg-success text-white p-4 rounded shadow">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h1 class="fw-bold mb-2"><i class="bi bi-receipt-cutoff me-2"></i>Order Receipt</h1>
            <p class="lead mb-0">Your order has been confirmed</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="home" class="text-white">Home</a></li>
                    <li class="breadcrumb-item"><a href="products" class="text-white">Products</a></li>
                    <li class="breadcrumb-item"><a href="cart" class="text-white">Cart</a></li>
                    <li class="breadcrumb-item active text-white-50" aria-current="page">Receipt</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-success text-white">
                <h2 class="h5 mb-0"><i class="bi bi-check-circle me-2"></i>Order Confirmed</h2>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i> Your order has been received!
                    </div>
                    <p class="text-muted">Order #<?= htmlspecialchars((string)$order['id']) ?> - <?= htmlspecialchars($order['ordered_at']) ?></p>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <p><strong>Order Status:</strong></p>
                                    <p><span class="badge bg-success"><?= htmlspecialchars($order['status']) ?></span></p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p><strong>Order Date:</strong></p>
                                    <p><?= htmlspecialchars($order['ordered_at']) ?></p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p><strong>Order Total:</strong></p>
                                    <p class="fw-bold">$<?= number_format((float)$order['total'], 2) ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($order['items'])): ?>
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Order Items</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Product</th>
                                                    <th class="text-center">Quantity</th>
                                                    <th class="text-center">Price</th>
                                                    <th class="text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($order['items'] as $item): ?>
                                                    <?php 
                                                        $itemName = isset($item['name']) ? htmlspecialchars($item['name']) : 'Unknown Product';
                                                        $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
                                                        $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
                                                        $itemTotal = $itemPrice * $itemQuantity;
                                                    ?>
                                                    <tr>
                                                        <td><?= $itemName ?></td>
                                                        <td class="text-center"><?= $itemQuantity ?></td>
                                                        <td class="text-center">$<?= number_format($itemPrice, 2) ?></td>
                                                        <td class="text-end">$<?= number_format($itemTotal, 2) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-info">
                            <p class="mb-0"><i class="bi bi-info-circle me-2"></i> This is a demonstration order. No actual payment has been processed.</p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="products" class="btn btn-primary">
                        <i class="bi bi-cart me-2"></i>Continue Shopping
                    </a>
                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer me-2"></i>Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> 