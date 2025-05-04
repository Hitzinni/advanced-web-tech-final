<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the config file
require_once '../config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}

// Database connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Get user's orders
try {
    // Initialize orders array
    $orders = [];
    $groupedOrders = [];
    
    // Check if orders table exists (new format)
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'orders'");
    $ordersTableExists = $tableCheck->rowCount() > 0;
    
    // Fetch orders from new table if it exists
    if ($ordersTableExists) {
        try {
            $sql = "SELECT o.*, COUNT(oi.id) as item_count
                   FROM `orders` o
                   LEFT JOIN order_items oi ON o.id = oi.order_id
                   WHERE o.user_id = ?
                   GROUP BY o.id
                   ORDER BY o.ordered_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user_id']]);
            $newOrders = $stmt->fetchAll();
            
            foreach ($newOrders as &$order) {
                $order['is_new_format'] = true;
                if (!isset($order['ordered_at'])) {
                    $order['ordered_at'] = date('Y-m-d H:i:s');
                }
                $order['price_at_order'] = $order['total_amount'] ?? 0;
                $order['product_name'] = 'Order #' . ($order['id'] ?? 'Unknown') . ' (' . ($order['item_count'] ?? 0) . ' items)';
                $order['category'] = $order['payment_method'] ?? 'Standard';
                $order['image_url'] = 'images/products/order-icon.jpg';
                
                // Add to orders array
                $orders[] = $order;
            }
        } catch (PDOException $e) {
            // Log error but continue with legacy orders
            error_log("Error fetching new format orders: " . $e->getMessage());
        }
    }
    
    // Check if legacy order table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'order'");
    $legacyTableExists = $tableCheck->rowCount() > 0;
    
    // Fetch orders from legacy table if it exists
    if ($legacyTableExists) {
        try {
            $sql = "SELECT o.*, p.name as product_name, p.category, p.image_url 
                    FROM `order` o
                    LEFT JOIN product p ON o.product_id = p.id
                    WHERE o.user_id = ?
                    ORDER BY o.ordered_at DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user_id']]);
            $legacyOrders = $stmt->fetchAll();
            
            foreach ($legacyOrders as &$order) {
                $order['is_new_format'] = false;
                
                // Provide defaults for missing data
                if (!isset($order['product_name'])) {
                    $order['product_name'] = 'Unknown Product';
                }
                if (!isset($order['ordered_at'])) {
                    $order['ordered_at'] = date('Y-m-d H:i:s');
                }
                
                // Ensure price consistency for view
                if (isset($order['price_at_order'])) {
                    $order['total'] = $order['price_at_order'];
                } elseif (isset($order['total'])) {
                    $order['price_at_order'] = $order['total'];
                } else {
                    $order['total'] = $order['price_at_order'] = 0;
                }
                
                // Add to orders array
                $orders[] = $order;
            }
        } catch (PDOException $e) {
            error_log("Error fetching legacy orders: " . $e->getMessage());
        }
    }
    
    // Sort all orders by date
    usort($orders, function($a, $b) {
        $dateA = $a['ordered_at'] ?? date('Y-m-d H:i:s');
        $dateB = $b['ordered_at'] ?? date('Y-m-d H:i:s');
        return strtotime($dateB) - strtotime($dateA);
    });
    
    // Group orders by date for display
    foreach ($orders as $order) {
        if (!isset($order['ordered_at'])) {
            continue;
        }
        
        $orderDate = date('Y-m-d', strtotime($order['ordered_at']));
        
        if (!isset($groupedOrders[$orderDate])) {
            $groupedOrders[$orderDate] = [];
        }
        
        $groupedOrders[$orderDate][] = $order;
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Error in My Orders: " . $e->getMessage());
    $error = "An error occurred while loading your orders. Please try again later.";
}

// Function to create URL with proper base path
function url($path, $params = []) {
    $url = $path;
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return $url;
}

// Page title
$pageTitle = 'My Orders';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Grocery Store</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Simple Header -->
    <header class="bg-primary text-white py-3 mb-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Grocery Store</h1>
                <nav>
                    <a href="index.php" class="text-white me-3">Home</a>
                    <a href="products" class="text-white me-3">Products</a>
                    <a href="cart" class="text-white me-3">Cart</a>
                    <a href="my-orders" class="text-white">My Orders</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container my-4">
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
                            <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
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

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if (empty($groupedOrders)): ?>
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
                                    $orderPrice = isset($order['price_at_order']) ? (float)$order['price_at_order'] : 
                                                  (isset($order['total']) ? (float)$order['total'] : 0);
                                    $dateTotal += $orderPrice;
                                }
                                
                                // Format date for display
                                try {
                                    $dateObj = new DateTime($date);
                                    $formattedDate = $dateObj->format('F j, Y');
                                } catch (Exception $e) {
                                    $formattedDate = $date;
                                }
                                
                                // Create a unique ID for the accordion item
                                $accordionId = 'order-' . str_replace('-', '', $date);
                            ?>
                            <div class="accordion-item mb-4 border-0 shadow">
                                <h2 class="accordion-header" id="heading<?= $accordionId ?>">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                            data-bs-target="#collapse<?= $accordionId ?>" aria-expanded="true" 
                                            aria-controls="collapse<?= $accordionId ?>">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <div>
                                                <i class="bi bi-calendar3 me-2"></i>
                                                <span class="fw-bold"><?= $formattedDate ?></span>
                                                <span class="badge bg-secondary ms-2"><?= $totalItems ?> <?= $totalItems === 1 ? 'order' : 'orders' ?></span>
                                            </div>
                                            <div class="text-success ms-auto me-3">
                                                <span class="fw-bold">$<?= number_format($dateTotal, 2) ?></span>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?= $accordionId ?>" class="accordion-collapse collapse show" 
                                     aria-labelledby="heading<?= $accordionId ?>">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive">
                                            <table class="table align-middle mb-0">
                                                <tbody>
                                                    <?php foreach ($orders as $order): ?>
                                                        <?php
                                                            // Ensure required order properties exist
                                                            $orderId = $order['id'] ?? 0;
                                                            $orderPrice = isset($order['price_at_order']) ? (float)$order['price_at_order'] : 
                                                                        (isset($order['total']) ? (float)$order['total'] : 0);
                                                            
                                                            // Determine the display image and icon
                                                            $imagePath = isset($order['image_url']) && !empty($order['image_url']) 
                                                                ? htmlspecialchars($order['image_url']) 
                                                                : 'images/products/placeholder.jpg';
                                                            
                                                            // Set icon class based on category
                                                            $iconClass = 'bag';
                                                            if (isset($order['category'])) {
                                                                $category = strtolower($order['category']);
                                                                if (strpos($category, 'fruit') !== false) {
                                                                    $iconClass = 'apple';
                                                                } elseif (strpos($category, 'veg') !== false) {
                                                                    $iconClass = 'flower2';
                                                                } elseif (strpos($category, 'meat') !== false) {
                                                                    $iconClass = 'basket';
                                                                } elseif (strpos($category, 'dairy') !== false) {
                                                                    $iconClass = 'cup';
                                                                }
                                                            }
                                                            
                                                            // Safely get the order time
                                                            $orderTime = 'Unknown time';
                                                            if (isset($order['ordered_at'])) {
                                                                try {
                                                                    $orderTimeObj = new DateTime($order['ordered_at']);
                                                                    $orderTime = $orderTimeObj->format('h:i A');
                                                                } catch (Exception $e) {
                                                                    // Keep default value
                                                                }
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
                                                                                } elseif ($status === 'Received') {
                                                                                    $statusClass = 'bg-success';
                                                                                } elseif ($status === 'Cancelled') {
                                                                                    $statusClass = 'bg-danger';
                                                                                }
                                                                            ?>
                                                                            <span class="badge <?= $statusClass ?> ms-2"><?= $status ?></span>
                                                                        <?php endif; ?>
                                                                        <p class="text-muted small mb-0">Order #<?= htmlspecialchars((string)$orderId) ?></p>
                                                                        <p class="text-muted small mb-0"><?= htmlspecialchars($orderTime) ?></p>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                $<?= number_format($orderPrice, 2) ?>
                                                            </td>
                                                            <td class="text-end align-middle">
                                                                <a href="order-receipt?id=<?= $orderId ?>" class="btn btn-sm btn-outline-primary">
                                                                    <i class="bi bi-receipt me-1"></i>View Receipt
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
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h5>Grocery Store</h5>
                    <p>Your one-stop shop for fresh groceries.</p>
                </div>
                <div class="col-lg-6 text-lg-end">
                    <p>&copy; <?= date('Y') ?> Grocery Store. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
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
            
            // Log all network requests to debug 404 errors
            console.log('Page URL:', window.location.href);
            console.log('Current path:', window.location.pathname);
        });
    </script>
</body>
</html> 