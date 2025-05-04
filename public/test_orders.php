<?php
// This is a test file with a different name
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Orders Page</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        h1 { color: #2c3e50; }
        .info { background: #e8f4f8; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Test Orders Page</h1>
    
    <div class="info">
        <p>This page is working correctly if you can see it.</p>
        <p>This is a test to see if a different file name works better than 'my-orders.php'.</p>
    </div>
    
    <p>Environment info:</p>
    <ul>
        <li>PHP Version: <?= phpversion() ?></li>
        <li>Server Software: <?= $_SERVER['SERVER_SOFTWARE'] ?></li>
        <li>Request URI: <?= $_SERVER['REQUEST_URI'] ?></li>
    </ul>
    
    <p><a href="my-orders.php">Try the my-orders.php file</a></p>
    <p><a href="direct-orders.php">Try the direct-orders.php file</a></p>
    <p><a href="index.php">Go to Homepage</a></p>
</body>
</html> 