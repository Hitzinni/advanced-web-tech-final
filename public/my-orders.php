<?php
// This is a simple test file to see if direct routing works
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Orders Test</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>My Orders Page Works!</h1>
        <div class="card">
            <p>If you're seeing this page, it means the direct PHP file route is working correctly.</p>
            <p>This is a very simple file that doesn't rely on any framework components, config files, or database access.</p>
        </div>
        <div class="card">
            <h2>Session Information:</h2>
            <pre><?php 
                session_start();
                echo "Session status: " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Not active") . "\n";
                echo "User logged in: " . (isset($_SESSION['user_id']) ? "Yes (ID: {$_SESSION['user_id']})" : "No") . "\n";
            ?></pre>
        </div>
        <div class="card">
            <h2>Navigation:</h2>
            <p><a href="index.php">Home</a> | <a href="direct-orders.php">Try Direct Orders Page</a></p>
        </div>
    </div>
</body>
</html> 