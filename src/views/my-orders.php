<!-- Page Header Section -->
<div class="category-header position-relative mb-4 bg-primary text-white p-4 rounded shadow">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h1 class="fw-bold mb-2"><i class="bi bi-bag-check me-2"></i>My Orders</h1>
            <p class="lead mb-0">View your order history</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="home" class="text-white">Home</a></li>
                    <li class="breadcrumb-item active text-white-50" aria-current="page">My Orders</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Status Update Note -->
<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle-fill me-2"></i>
    <strong>Tip:</strong> Click on <strong>"View Receipt"</strong> to see order details. You can mark delivered orders as "received" from the receipt page.
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'manager'): ?>
        <span class="ms-2 badge bg-primary">Admin: You can update order status from the receipt page.</span>
    <?php endif; ?>
</div>

<?php if (empty($groupedOrders) || count($groupedOrders) === 0): ?>
<!-- Empty Orders Display -->
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0 p-4 text-center">
            <div class="empty-orders-icon mb-4">
                <i class="bi bi-bag-x text-muted" style="font-size: 5rem;"></i>
            </div>
            <h2 class="h4 mb-3">You haven't placed any orders yet</h2>
            <p class="text-muted mb-4">Start shopping to see your order history here.</p>
            <a href="products" class="btn btn-primary">
                <i class="bi bi-basket me-2"></i>Browse Products
            </a>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Orders Display -->
<div class="row">
    <div class="col-lg-12">
        <div class="accordion" id="ordersAccordion">
            <?php foreach ($groupedOrders as $date => $orders): ?>
                <?php
                    // Calculate total for all orders on this date
                    $dateTotal = 0;
                    $totalItems = count($orders);
                    foreach ($orders as $order) {
                        $dateTotal += (float)$order['price_at_order'];
                    }
                    
                    // Format date for display
                    try {
                        $dateObj = new DateTime($date);
                        $formattedDate = $dateObj->format('F j, Y');
                    } catch (Exception $e) {
                        error_log('Error formatting date: ' . $e->getMessage());
                        $formattedDate = $date;
                    }
                    
                    // Create a unique ID for the accordion item
                    $accordionId = 'order-' . str_replace('-', '', $date);
                ?>
                <div class="accordion-item mb-3 border rounded shadow-sm">
                    <h2 class="accordion-header" id="heading-<?= $accordionId ?>">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $accordionId ?>" aria-expanded="true" aria-controls="collapse-<?= $accordionId ?>">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold"><?= htmlspecialchars($formattedDate) ?></span>
                                    <span class="badge bg-primary ms-2"><?= $totalItems ?> <?= $totalItems === 1 ? 'item' : 'items' ?></span>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold">$<?= number_format($dateTotal, 2) ?></span>
                                </div>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-<?= $accordionId ?>" class="accordion-collapse collapse show" aria-labelledby="heading-<?= $accordionId ?>" data-bs-parent="#ordersAccordion">
                        <div class="accordion-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Product</th>
                                            <th scope="col" class="text-center">Price</th>
                                            <th scope="col" class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <?php 
                                                // Fix image path handling
                                                $imagePath = $order['image_url'] ?? '';
                                                
                                                // Remove 'public/' prefix if it exists
                                                if (strpos($imagePath, 'public/') === 0) {
                                                    $imagePath = substr($imagePath, 7); // Remove 'public/'
                                                }
                                                
                                                // Remove leading slash if present
                                                if (strpos($imagePath, '/') === 0) {
                                                    $imagePath = substr($imagePath, 1);
                                                }
                                                
                                                // Default icon based on order type or category
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
                                                } elseif (strpos($category, 'Cash') !== false) {
                                                    $iconClass = 'cash';
                                                } elseif (strpos($category, 'Credit') !== false) {
                                                    $iconClass = 'credit-card';
                                                }
                                                
                                                if (isset($order['is_new_format']) && $order['is_new_format']) {
                                                    $iconClass = 'bag-check';
                                                }
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-3">
                                                            <div class="bg-light rounded overflow-hidden" style="width: 60px; height: 60px;">
                                                                <img src="<?= htmlspecialchars($imagePath) ?>" 
                                                                     alt="<?= htmlspecialchars($order['product_name'] ?? 'Product') ?>" 
                                                                     class="w-100 h-100" 
                                                                     style="object-fit: cover;" 
                                                                     onerror="this.onerror=null; this.src='images/products/placeholder.jpg'; this.parentNode.innerHTML='<div class=\'text-center p-2\'><i class=\'bi bi-<?= $iconClass ?> text-primary\' style=\'font-size: 1.5rem; line-height: 40px;\'></i></div>'">
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1"><?= htmlspecialchars($order['product_name'] ?? 'Unknown Product') ?></h6>
                                                                <span class="badge bg-info text-white"><?= htmlspecialchars($order['category'] ?? 'Unknown') ?></span>
                                                                <?php if (isset($order['is_new_format']) && $order['is_new_format']): ?>
                                                                    <?php
                                                                        $statusClass = 'bg-secondary';
                                                                        $status = isset($order['status']) ? ucfirst($order['status']) : 'Processing';
                                                                        
                                                                        if ($status === 'Pending') {
                                                                            $statusClass = 'bg-warning text-dark';
                                                                        } elseif ($status === 'Processing') {
                                                                            $statusClass = 'bg-info';
                                                                        } elseif ($status === 'Shipped') {
                                                                            $statusClass = 'bg-primary';
                                                                        } elseif ($status === 'Delivered') {
                                                                            $statusClass = 'bg-success';
                                                                        } elseif ($status === 'Cancelled') {
                                                                            $statusClass = 'bg-danger';
                                                                        }
                                                                    ?>
                                                                    <span class="badge <?= $statusClass ?> ms-2"><?= $status ?></span>
                                                                <?php endif; ?>
                                                                <p class="text-muted small mb-0">Order #<?= htmlspecialchars($order['id'] ?? '0') ?></p>
                                                                <p class="text-muted small mb-0"><?= isset($order['ordered_at']) ? htmlspecialchars((new DateTime($order['ordered_at']))->format('h:i A')) : 'Unknown time' ?></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    $<?= number_format((float)($order['price_at_order'] ?? 0), 2) ?>
                                                </td>
                                                <td class="text-end align-middle">
                                                    <a href="order-receipt?id=<?= (int)($order['id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-receipt me-1"></i>View Receipt
                                                        <?php if (isset($order['is_new_format']) && $order['is_new_format'] && isset($order['status'])): ?>
                                                            <?php if ($order['status'] === 'delivered'): ?>
                                                                <span class="badge bg-success ms-1">Mark Received</span>
                                                            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'manager'): ?>
                                                                <span class="badge bg-secondary ms-1">Update Status</span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </a>
                                                    <a href="products" class="btn btn-sm btn-success">
                                                        <i class="bi bi-cart-plus me-1"></i>Buy Again
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure only the first accordion item is expanded initially
    const accordionItems = document.querySelectorAll('.accordion-item');
    if (accordionItems.length > 0) {
        for (let i = 1; i < accordionItems.length; i++) {
            const collapseElement = accordionItems[i].querySelector('.accordion-collapse');
            if (collapseElement) {
                collapseElement.classList.remove('show');
            }
        }
    }
});
</script> 