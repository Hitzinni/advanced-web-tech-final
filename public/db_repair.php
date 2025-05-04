<?php
// Show all PHP errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Basic HTML structure
echo '<!DOCTYPE html>
<html>
<head>
    <title>Database Repair Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2, h3 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; }
        .section { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        form { margin-bottom: 20px; }
        button, input[type="submit"] { background: #4CAF50; color: white; padding: 10px 15px; 
                 border: none; border-radius: 4px; cursor: pointer; }
        button:hover, input[type="submit"]:hover { background: #45a049; }
    </style>
</head>
<body>
    <h1>Database Repair Tool</h1>';

// Verify database connection
echo '<div class="section">';
echo '<h2>Database Connection</h2>';

// Get configuration
try {
    // Include the config file directly instead of using parse_ini_file
    require_once '../config.php';
    
    $host = DB_HOST;
    $dbname = DB_NAME;
    $user = DB_USER;
    $pass = DB_PASS;
    
    if (empty($dbname)) {
        throw new Exception('Database name not set in config.php');
    }
    
    echo "<p>Connecting to database: <strong>$dbname</strong> on <strong>$host</strong></p>";
    
    // Connect to database
    $dsn = "mysql:host=$host;dbname=$dbname";
    $db = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo '<p class="success">✓ Connection successful!</p>';
} catch (Exception $e) {
    echo '<p class="error">✗ Database connection error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    exit('</div></body></html>');
}
echo '</div>';

// Function to check if a table exists
function tableExists($db, $table) {
    $stmt = $db->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    return $stmt->rowCount() > 0;
}

// Check existing tables
echo '<div class="section">';
echo '<h2>Database Structure Check</h2>';

$requiredTables = [
    'user' => "
        CREATE TABLE `user` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `email` varchar(255) NOT NULL,
          `password` varchar(255) NOT NULL,
          `phone` varchar(20) DEFAULT NULL,
          `role` varchar(20) DEFAULT 'user',
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'product' => "
        CREATE TABLE `product` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `price` decimal(10,2) NOT NULL,
          `description` text DEFAULT NULL,
          `category` varchar(100) DEFAULT NULL,
          `image_url` varchar(255) DEFAULT NULL,
          `stock` int(11) DEFAULT 100,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'order' => "
        CREATE TABLE `order` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `product_id` int(11) NOT NULL,
          `price_at_order` decimal(10,2) NOT NULL,
          `ordered_at` datetime DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`),
          KEY `product_id` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'orders' => "
        CREATE TABLE `orders` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `total_amount` decimal(10,2) NOT NULL,
          `subtotal` decimal(10,2) NOT NULL,
          `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
          `shipping_address` text NOT NULL,
          `payment_method` varchar(50) NOT NULL DEFAULT 'Cash on Delivery',
          `status` varchar(20) NOT NULL DEFAULT 'pending',
          `ordered_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'order_items' => "
        CREATE TABLE `order_items` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `order_id` int(11) NOT NULL,
          `product_id` int(11) NOT NULL,
          `product_name` varchar(255) NOT NULL,
          `price` decimal(10,2) NOT NULL,
          `quantity` int(11) NOT NULL DEFAULT 1,
          PRIMARY KEY (`id`),
          KEY `order_id` (`order_id`),
          KEY `product_id` (`product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'shipping_address' => "
        CREATE TABLE `shipping_address` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `first_name` varchar(100) NOT NULL,
          `last_name` varchar(100) NOT NULL,
          `address` varchar(255) NOT NULL,
          `city` varchar(100) NOT NULL,
          `state` varchar(100) NOT NULL,
          `zip` varchar(20) NOT NULL,
          `phone` varchar(20) DEFAULT NULL,
          `is_default` tinyint(1) NOT NULL DEFAULT 0,
          `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    "
];

// Check each required table
$missingTables = [];
echo '<h3>Table Status:</h3>';
echo '<table>';
echo '<tr><th>Table</th><th>Status</th></tr>';

foreach ($requiredTables as $table => $createSql) {
    $exists = tableExists($db, $table);
    if ($exists) {
        echo "<tr><td>$table</td><td class='success'>Exists</td></tr>";
    } else {
        echo "<tr><td>$table</td><td class='error'>Missing</td></tr>";
        $missingTables[$table] = $createSql;
    }
}

echo '</table>';

// Create missing tables if requested
if (!empty($missingTables) && isset($_POST['fix_tables'])) {
    echo '<h3>Creating Missing Tables:</h3>';
    
    foreach ($missingTables as $table => $createSql) {
        try {
            $db->exec($createSql);
            echo "<p class='success'>✓ Created table '$table' successfully</p>";
        } catch (Exception $e) {
            echo "<p class='error'>✗ Failed to create table '$table': " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // Refresh the page to show updated table status
    echo '<p>Refreshing page to show updated table status...</p>';
    echo '<script>setTimeout(function() { window.location.reload(); }, 2000);</script>';
}

// Show repair form if tables are missing
if (!empty($missingTables)) {
    echo '<form method="post">';
    echo '<p class="warning">Warning: Some required tables are missing. You can create them automatically:</p>';
    echo '<input type="submit" name="fix_tables" value="Create Missing Tables">';
    echo '</form>';
}

echo '</div>';

// Check for column issues in existing tables
echo '<div class="section">';
echo '<h2>Column Structure Check</h2>';

// Check for old columns and missing columns
try {
    // Tables that exist
    $existingTables = [];
    $stmt = $db->query('SHOW TABLES');
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $existingTables[] = $row[0];
    }
    
    // Check each table
    $tableIssues = [];
    
    foreach ($existingTables as $table) {
        // Get columns
        $columns = [];
        $stmt = $db->query("DESCRIBE `$table`");
        while ($row = $stmt->fetch()) {
            $columns[$row['Field']] = $row;
        }
        
        // Check for specific missing columns based on table
        switch ($table) {
            case 'orders':
                if (!isset($columns['shipping_fee'])) {
                    $tableIssues[$table][] = "Missing column 'shipping_fee'";
                }
                if (!isset($columns['status'])) {
                    $tableIssues[$table][] = "Missing column 'status'";
                }
                break;
                
            case 'user':
                if (!isset($columns['role'])) {
                    $tableIssues[$table][] = "Missing column 'role'";
                }
                break;
                
            case 'order':
                if (!isset($columns['ordered_at'])) {
                    $tableIssues[$table][] = "Missing column 'ordered_at'";
                }
                break;
        }
    }
    
    // Display results
    if (empty($tableIssues)) {
        echo '<p class="success">✓ All tables have the expected columns</p>';
    } else {
        echo '<h3>Column Issues Found:</h3>';
        echo '<table>';
        echo '<tr><th>Table</th><th>Issues</th></tr>';
        
        foreach ($tableIssues as $table => $issues) {
            echo "<tr><td>$table</td><td>";
            foreach ($issues as $issue) {
                echo "<div class='warning'>$issue</div>";
            }
            echo "</td></tr>";
        }
        
        echo '</table>';
        
        // Show fix form
        echo '<form method="post">';
        echo '<input type="submit" name="fix_columns" value="Fix Column Issues">';
        echo '</form>';
        
        // Fix column issues if requested
        if (isset($_POST['fix_columns'])) {
            echo '<h3>Fixing Column Issues:</h3>';
            
            foreach ($tableIssues as $table => $issues) {
                foreach ($issues as $issue) {
                    try {
                        if (strpos($issue, "Missing column 'shipping_fee'") !== false) {
                            $db->exec("ALTER TABLE `$table` ADD COLUMN `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `subtotal`");
                            echo "<p class='success'>✓ Added 'shipping_fee' column to '$table'</p>";
                        }
                        else if (strpos($issue, "Missing column 'status'") !== false) {
                            $db->exec("ALTER TABLE `$table` ADD COLUMN `status` varchar(20) NOT NULL DEFAULT 'pending' AFTER `payment_method`");
                            echo "<p class='success'>✓ Added 'status' column to '$table'</p>";
                        }
                        else if (strpos($issue, "Missing column 'role'") !== false) {
                            $db->exec("ALTER TABLE `$table` ADD COLUMN `role` varchar(20) DEFAULT 'user' AFTER `phone`");
                            echo "<p class='success'>✓ Added 'role' column to '$table'</p>";
                        }
                        else if (strpos($issue, "Missing column 'ordered_at'") !== false) {
                            $db->exec("ALTER TABLE `$table` ADD COLUMN `ordered_at` datetime DEFAULT CURRENT_TIMESTAMP");
                            echo "<p class='success'>✓ Added 'ordered_at' column to '$table'</p>";
                        }
                    } catch (Exception $e) {
                        echo "<p class='error'>✗ Failed to fix issue in '$table': " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
            }
            
            // Refresh the page to show updated column status
            echo '<p>Refreshing page to show updated column status...</p>';
            echo '<script>setTimeout(function() { window.location.reload(); }, 2000);</script>';
        }
    }
} catch (Exception $e) {
    echo '<p class="error">✗ Error checking columns: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '</div>';

// Provide navigation links
echo '<div class="section">';
echo '<h2>Navigation</h2>';
echo '<p><a href="error_info.php">Go to Error Info Page</a></p>';
echo '<p><a href="my-orders">Return to My Orders</a></p>';
echo '<p><a href="index.php">Return to Home Page</a></p>';
echo '</div>';

echo '</body></html>';
?> 