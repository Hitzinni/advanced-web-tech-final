<!-- My Orders Header Section -->
<div class="category-header position-relative mb-4 bg-primary text-white p-4 rounded shadow">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h1 class="fw-bold mb-2"><i class="bi bi-clock-history me-2"></i>My Orders</h1>
            <p class="lead mb-0">View your order history</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="home" class="text-white">Home</a></li>
                    <li class="breadcrumb-item"><a href="products" class="text-white">Products</a></li>
                    <li class="breadcrumb-item active text-white-50" aria-current="page">My Orders</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Orders Content -->
<div class="row">
    <div class="col-12">
        <!-- No Orders Message (displayed only if no orders exist) -->
        <?php if (empty($groupedOrders)): ?>
            <div class="card shadow border-0 mb-4">
                <div class="card-body text-center py-5">
                    <div class="py-4">
                        <i class="bi bi-bag-x text-primary" style="font-size: 5rem;"></i>
                        <h3 class="mt-4 mb-3">No Order History</h3>
                        <p class="text-muted mb-4 px-4 mx-auto" style="max-width: 500px;">
                            You haven't placed any orders yet. Browse our products and make your first purchase!
                        </p>
                        <a href="products" class="btn btn-primary px-4 py-2">
                            <i class="bi bi-bag-plus me-2"></i>Browse Products
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Orders By Date -->
            <?php foreach ($groupedOrders as $orderDate => $orders): ?>
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-light py-3">
                        <h2 class="h5 mb-0">
                            <i class="bi bi-calendar3 me-2"></i>Orders from <?= date('F j, Y', strtotime($orderDate)) ?>
                        </h2>
                    </div>
                    <div class="card-body p-0">
                        <!-- Individual Orders -->
                        <?php foreach ($orders as $index => $order): ?>
                            <div class="p-4 <?= $index > 0 ? 'border-top' : '' ?>">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h3 class="h6 fw-bold">
                                            <i class="bi bi-receipt me-2"></i>Order #<?= htmlspecialchars($order['id']) ?>
                                            <span class="badge bg-success ms-2"><?= htmlspecialchars($order['status']) ?></span>
                                        </h3>
                                        <p class="text-muted small">
                                            Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['ordered_at'])) ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <p class="fw-bold mb-2">Total: $<?= number_format($order['total'], 2) ?></p>
                                        <a href="order-receipt?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>View Details
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Order Items Summary -->
                                <?php if (!empty($order['items'])): ?>
                                    <div class="mt-3">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th scope="col">Product</th>
                                                        <th scope="col" class="text-center">Quantity</th>
                                                        <th scope="col" class="text-end">Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($order['items'] as $item): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($item['name']) ?></td>
                                                            <td class="text-center"><?= $item['quantity'] ?></td>
                                                            <td class="text-end">$<?= number_format($item['price'], 2) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Back to Shopping Button -->
            <div class="text-center mb-4">
                <a href="products" class="btn btn-primary">
                    <i class="bi bi-cart me-2"></i>Continue Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</div> 