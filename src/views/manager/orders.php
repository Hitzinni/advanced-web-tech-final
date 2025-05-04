<!-- Custom CSS for manager pages -->
<link rel="stylesheet" href="<?= \App\Helpers\View::asset('css/manager.css') ?>">

<!-- Page Header Section -->
<div class="category-header position-relative mb-4 bg-primary text-white p-4 rounded shadow">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h1 class="fw-bold mb-2"><i class="bi bi-box-seam me-2"></i>Order Management</h1>
            <p class="lead mb-0">Manage all customer orders</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="home" class="text-white">Home</a></li>
                    <li class="breadcrumb-item active text-white-50" aria-current="page">Order Management</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?> alert-dismissible fade show mb-4" role="alert">
        <?= $_SESSION['flash_message']['text'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<!-- Filter Controls -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0"><i class="bi bi-funnel me-2"></i>Filter Orders</h2>
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="bi bi-sliders me-1"></i> Show/Hide Filters
            </button>
        </div>
    </div>
    <div class="collapse show" id="filterCollapse">
        <div class="card-body p-3">
            <form action="manager-orders" method="get" class="row g-3">
                <div class="col-md-4">
                    <label for="status-filter" class="form-label">Order Status</label>
                    <select class="form-select" id="status-filter" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= isset($_GET['status']) && $_GET['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="processing" <?= isset($_GET['status']) && $_GET['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                        <option value="shipped" <?= isset($_GET['status']) && $_GET['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                        <option value="delivered" <?= isset($_GET['status']) && $_GET['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="received" <?= isset($_GET['status']) && $_GET['status'] === 'received' ? 'selected' : '' ?>>Received</option>
                        <option value="cancelled" <?= isset($_GET['status']) && $_GET['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="date-filter" class="form-label">Order Date</label>
                    <select class="form-select" id="date-filter" name="date_range">
                        <option value="">All Time</option>
                        <option value="today" <?= isset($_GET['date_range']) && $_GET['date_range'] === 'today' ? 'selected' : '' ?>>Today</option>
                        <option value="week" <?= isset($_GET['date_range']) && $_GET['date_range'] === 'week' ? 'selected' : '' ?>>Last 7 Days</option>
                        <option value="month" <?= isset($_GET['date_range']) && $_GET['date_range'] === 'month' ? 'selected' : '' ?>>Last 30 Days</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Search Orders</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Order ID or customer email" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Apply Filters
                    </button>
                    <a href="manager-orders" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light py-3">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0"><i class="bi bi-list-ul me-2"></i>All Orders</h2>
            <span class="badge bg-primary"><?= count($orders) ?> Orders</span>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($orders)): ?>
            <div class="alert alert-info m-3 mb-0">
                <i class="bi bi-info-circle me-2"></i> No orders found matching your criteria.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Order ID</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Date</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
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
                                } elseif ($status === 'Received') {
                                    $statusClass = 'bg-success';
                                } elseif ($status === 'Cancelled') {
                                    $statusClass = 'bg-danger';
                                }
                                
                                $formattedDate = date('M j, Y g:i A', strtotime($order['ordered_at']));
                                $total = isset($order['total_amount']) ? $order['total_amount'] : $order['price_at_order'];
                                $formattedTotal = number_format($total, 2);
                                
                                $customerName = isset($order['user_name']) ? $order['user_name'] : 'Unknown';
                                $customerEmail = isset($order['email']) ? $order['email'] : 'Unknown';
                            ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($customerName) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($customerEmail) ?></div>
                                    </div>
                                </td>
                                <td><?= $formattedDate ?></td>
                                <td>$<?= $formattedTotal ?></td>
                                <td><span class="badge <?= $statusClass ?>"><?= $status ?></span></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="order-receipt?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i> View
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bi bi-gear-fill me-1"></i> Update Status
                                        </button>
                                        <ul class="dropdown-menu scrollable">
                                            <li><h6 class="dropdown-header">Change Status</h6></li>
                                            <li><a class="dropdown-item <?= ($status === 'Pending') ? 'active' : '' ?>" href="update-order-status?id=<?= $order['id'] ?>&status=pending&redirect=manager">Pending</a></li>
                                            <li><a class="dropdown-item <?= ($status === 'Processing') ? 'active' : '' ?>" href="update-order-status?id=<?= $order['id'] ?>&status=processing&redirect=manager">Processing</a></li>
                                            <li><a class="dropdown-item <?= ($status === 'Shipped') ? 'active' : '' ?>" href="update-order-status?id=<?= $order['id'] ?>&status=shipped&redirect=manager">Shipped</a></li>
                                            <li><a class="dropdown-item <?= ($status === 'Delivered') ? 'active' : '' ?>" href="update-order-status?id=<?= $order['id'] ?>&status=delivered&redirect=manager">Delivered</a></li>
                                            <li><a class="dropdown-item <?= ($status === 'Received') ? 'active' : '' ?>" href="update-order-status?id=<?= $order['id'] ?>&status=received&redirect=manager">Received</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item <?= ($status === 'Cancelled') ? 'active' : '' ?>" href="update-order-status?id=<?= $order['id'] ?>&status=cancelled&redirect=manager">Cancelled</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Admin Instructions -->
<div class="card bg-light mb-4">
    <div class="card-body">
        <h3 class="h5 mb-3"><i class="bi bi-info-circle me-2"></i>Managing Orders</h3>
        <p>As an administrator, you can:</p>
        <ul>
            <li>View all customer orders in the system</li>
            <li>Filter orders by status, date range, or search terms</li>
            <li>Update the status of any order using the dropdown menu</li>
            <li>View complete order details by clicking on the "View" button</li>
        </ul>
        <p class="mb-0 text-danger"><strong>Note:</strong> Changing an order's status will send automatic notifications to the customer. Please ensure status changes are accurate.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle filter collapse state preservation
    const filterCollapse = document.getElementById('filterCollapse');
    const showFilters = localStorage.getItem('showOrderFilters') === 'true';
    
    if (filterCollapse) {
        if (!showFilters) {
            filterCollapse.classList.remove('show');
        }
        
        filterCollapse.addEventListener('hidden.bs.collapse', function () {
            localStorage.setItem('showOrderFilters', 'false');
        });
        
        filterCollapse.addEventListener('shown.bs.collapse', function () {
            localStorage.setItem('showOrderFilters', 'true');
        });
    }
    
    // Custom dropdown handling for status updates
    const statusButtons = document.querySelectorAll('.table .dropdown-toggle');
    
    statusButtons.forEach(button => {
        // Remove Bootstrap data attribute to prevent default behavior
        button.removeAttribute('data-bs-toggle');
        
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Get the dropdown menu
            const menu = this.nextElementSibling;
            
            // Toggle visibility
            const isVisible = menu.classList.contains('show');
            
            // Close any open dropdowns first
            document.querySelectorAll('.dropdown-menu.show').forEach(openMenu => {
                openMenu.classList.remove('show');
            });
            
            if (!isVisible) {
                // Get position of button for accurate placement
                const buttonRect = this.getBoundingClientRect();
                
                // Calculate if we have enough space below
                const spaceBelow = window.innerHeight - buttonRect.bottom;
                const menuHeight = Math.min(menu.scrollHeight, 300); // Max 300px
                
                // Position the menu
                menu.classList.add('show');
                
                if (spaceBelow >= menuHeight) {
                    // Position below button
                    menu.style.top = `${buttonRect.bottom}px`;
                    menu.style.left = `${buttonRect.left}px`;
                    menu.style.maxHeight = '300px';
                } else {
                    // Position above button
                    menu.style.bottom = `${window.innerHeight - buttonRect.top}px`;
                    menu.style.left = `${buttonRect.left}px`;
                    menu.style.top = 'auto';
                    menu.style.maxHeight = '300px';
                }
                
                // Stop menu clicks from closing it
                menu.addEventListener('click', function(event) {
                    event.stopPropagation();
                }, { once: true });
            }
        });
    });
    
    // Close dropdowns when clicking elsewhere
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });
});
</script> 