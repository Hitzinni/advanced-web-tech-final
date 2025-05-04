<?php
// Special navigation helper for troubleshooting
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Navigation Helper</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        h1 { color: #2c3e50; }
        .nav-card { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 5px; 
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .nav-section { margin-bottom: 20px; }
        .nav-link { 
            display: block; 
            padding: 10px 15px; 
            background: #3498db; 
            color: white; 
            text-decoration: none; 
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .nav-link:hover { background: #2980b9; }
        .warning { color: #e74c3c; }
        .session-info { 
            background: #eee; 
            padding: 10px; 
            border-radius: 5px; 
            margin-top: 20px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <h1>Navigation Helper</h1>
    <p>Use this page to navigate to working pages and bypass routing issues.</p>
    
    <div class="nav-card">
        <h2>Working Pages</h2>
        <div class="nav-section">
            <a href="direct-orders.php" class="nav-link">My Orders (direct-orders.php)</a>
            <a href="index.php" class="nav-link">Home Page</a>
            <a href="products" class="nav-link">Products</a>
            <a href="cart" class="nav-link">Cart</a>
        </div>
        
        <h2>Test Pages</h2>
        <div class="nav-section">
            <a href="minimal-orders.php" class="nav-link">Minimal Orders Test</a>
            <a href="test_orders.php" class="nav-link">Test Orders</a>
            <a href="orders_static.html" class="nav-link">Static HTML Orders</a>
        </div>
    </div>
    
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="nav-card">
        <h2>Session Information</h2>
        <div class="session-info">
            <p>User ID: <?= htmlspecialchars($_SESSION['user_id']) ?></p>
            <p>User Role: <?= htmlspecialchars($_SESSION['role'] ?? 'Not set') ?></p>
        </div>
    </div>
    <?php else: ?>
    <div class="nav-card warning">
        <h2>Not Logged In</h2>
        <p>You are not currently logged in. Some features may not be available.</p>
        <a href="login" class="nav-link">Log In</a>
    </div>
    <?php endif; ?>
</body>
</html> 