<?php
// This is a direct test script to diagnose cart display issues
declare(strict_types=1);

// Start session
session_start();

// Define the base path
define('BASE_PATH', dirname(__DIR__));

// Output basic header
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cart Test Page</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4">Cart Test Page</h1>
        <div class="alert alert-info">
            <p><strong>Test Time:</strong> <?= date('Y-m-d H:i:s') ?></p>
            <p><strong>BASE_PATH:</strong> <?= BASE_PATH ?></p>
            <p><strong>Script Path:</strong> <?= $_SERVER['SCRIPT_FILENAME'] ?></p>
        </div>
        
        <?php
        // Check if we can find the cart template
        $cartTemplate = BASE_PATH . '/src/views/cart.php';
        if (file_exists($cartTemplate)) {
            echo '<div class="alert alert-success">Cart template found at: ' . $cartTemplate . '</div>';
            
            // Create a simple cart
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
            
            // Extract cart data
            $cartItems = $_SESSION['cart']['items'];
            $cartTotal = $_SESSION['cart']['total'];
            
            // Display cart data
            echo '<div class="card mb-4">';
            echo '<div class="card-header">Cart Contents</div>';
            echo '<div class="card-body">';
            echo '<table class="table table-striped">';
            echo '<thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th></tr></thead>';
            echo '<tbody>';
            
            foreach ($cartItems as $item) {
                $itemName = isset($item['name']) ? htmlspecialchars((string)$item['name']) : 'Unknown';
                $itemPrice = isset($item['price']) ? (float)$item['price'] : 0;
                $itemQuantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                $itemTotal = $itemPrice * $itemQuantity;
                
                echo '<tr>';
                echo '<td>' . $itemName . '</td>';
                echo '<td>$' . number_format($itemPrice, 2) . '</td>';
                echo '<td>' . $itemQuantity . '</td>';
                echo '<td>$' . number_format($itemTotal, 2) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody>';
            echo '<tfoot><tr><th colspan="3" class="text-end">Total:</th><th>$' . number_format($cartTotal, 2) . '</th></tr></tfoot>';
            echo '</table>';
            echo '</div>';
            echo '</div>';
            
            echo '<div class="alert alert-warning">';
            echo '<p><strong>Note:</strong> The above table shows data directly from the session. Below is an attempt to include the actual cart template:</p>';
            echo '</div>';
            
            // Try to include the template directly
            try {
                echo '<div class="card">';
                echo '<div class="card-header bg-primary text-white">Direct Template Include Result</div>';
                echo '<div class="card-body">';
                
                // Define helper functions if needed
                if (!class_exists('\App\Helpers\View')) {
                    echo '<div class="alert alert-danger">View helper class not found, creating a minimal version</div>';
                    
                    class MinimalViewHelper {
                        public static function url($route, $params = []) {
                            $baseUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/';
                            return $baseUrl . $route . (!empty($params) ? '?' . http_build_query($params) : '');
                        }
                        
                        public static function asset($path) {
                            $baseUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/';
                            return $baseUrl . $path;
                        }
                    }
                    
                    if (!class_exists('\App\Helpers\View')) {
                        class_alias('MinimalViewHelper', '\App\Helpers\View');
                    }
                }
                
                // Include the template (this might cause errors if it depends on other components)
                include $cartTemplate;
                
                echo '</div>';
                echo '</div>';
            } catch (\Throwable $e) {
                echo '<div class="alert alert-danger">';
                echo '<p><strong>Error including template:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<p><strong>In file:</strong> ' . htmlspecialchars($e->getFile()) . ' on line ' . $e->getLine() . '</p>';
                echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                echo '</div>';
            }
            
        } else {
            echo '<div class="alert alert-danger">Cart template NOT found at: ' . $cartTemplate . '</div>';
            
            // List files in the views directory
            $viewsDir = BASE_PATH . '/src/views';
            if (is_dir($viewsDir)) {
                $files = scandir($viewsDir);
                echo '<div class="card">';
                echo '<div class="card-header">Files in views directory</div>';
                echo '<div class="card-body">';
                echo '<ul>';
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..') {
                        echo '<li>' . htmlspecialchars($file) . ' - ' . (is_dir($viewsDir . '/' . $file) ? 'Directory' : 'File') . '</li>';
                    }
                }
                echo '</ul>';
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="alert alert-danger">Views directory not found at: ' . $viewsDir . '</div>';
            }
        }
        ?>
        
        <div class="mt-4">
            <a href="index.php" class="btn btn-primary">Return to Homepage</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 