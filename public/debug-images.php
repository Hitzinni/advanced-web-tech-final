<?php
// Debug page for product images
require_once dirname(__DIR__) . '/src/Database/Connection.php';
$db = \App\Database\Connection::getInstance()->getConnection();

// Get product list
$stmt = $db->prepare("SELECT id, name, category, image_url FROM product ORDER BY category, name");
$stmt->execute();
$products = $stmt->fetchAll();

// Display debugging information
echo "<h1>Product Image Debug</h1>";
echo "<p>Server document root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Current script path: " . __FILE__ . "</p>";
echo "<p>Current working directory: " . getcwd() . "</p>";

// Test if we can access images directory
$imagesDirExists = is_dir('images') ? 'exists' : 'does not exist';
$productsDirExists = is_dir('images/products') ? 'exists' : 'does not exist';
echo "<p>Directory 'images' $imagesDirExists</p>";
echo "<p>Directory 'images/products' $productsDirExists</p>";

// Display products and their images
echo "<h2>Products in database</h2>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Name</th><th>Category</th><th>Image URL</th><th>Exists?</th><th>Preview</th></tr>";

foreach($products as $product) {
    // Check if original image exists
    $originalExists = file_exists($product['image_url']) ? 'Yes' : 'No';
    
    // Try different path variations
    $imagePath = $product['image_url'];
    $publicImagePath = 'public/' . str_replace('public/', '', $imagePath);
    $strippedImagePath = str_replace('public/', '', $imagePath);
    
    $publicExists = file_exists($publicImagePath) ? 'Yes' : 'No';
    $strippedExists = file_exists($strippedImagePath) ? 'Yes' : 'No';
    
    echo "<tr>";
    echo "<td>" . $product['id'] . "</td>";
    echo "<td>" . htmlspecialchars($product['name']) . "</td>";
    echo "<td>" . htmlspecialchars($product['category']) . "</td>";
    echo "<td>" . htmlspecialchars($product['image_url']) . "</td>";
    echo "<td>Original: $originalExists<br>Public: $publicExists<br>Stripped: $strippedExists</td>";
    echo "<td><img src='" . htmlspecialchars($strippedImagePath) . "' style='max-width: 100px; max-height: 100px;' onerror=\"this.src='images/products/placeholder.jpg'; this.style.border='1px solid red';\"></td>";
    echo "</tr>";
}

echo "</table>"; 