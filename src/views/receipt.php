<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h1 class="h4 mb-0">Order Receipt</h1>
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
</div> 