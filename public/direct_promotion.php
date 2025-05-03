<?php
// Direct script to apply the promotion to the cart
session_start();

// Display header
echo "<h1>Direct Promotion Manager</h1>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

// Check if cart exists
if (!isset($_SESSION['cart']) || !isset($_SESSION['cart']['items'])) {
    echo "<p>No cart found in session.</p>";
    echo "<a href='/cart'>Go to cart</a>";
    exit;
}

// Get cart items
$cartItems = $_SESSION['cart']['items'];
echo "<p>Found " . count($cartItems) . " items in cart.</p>";

// Debug: Output cart items
echo "<h2>Current Cart Items:</h2>";
echo "<ul>";
foreach ($cartItems as $index => $item) {
    $category = isset($item['category']) ? (is_array($item['category']) ? implode(',', $item['category']) : $item['category']) : 'Unknown';
    $quantity = isset($item['quantity']) ? $item['quantity'] : '?';
    echo "<li>[$index] {$item['name']} - Category: $category, Quantity: $quantity, Price: \${$item['price']}</li>";
}
echo "</ul>";

// Apply the promotion directly
// Track categories and quantities
$meatCount = 0;
$fruitCount = 0;
$vegetableCount = 0;

// First pass: count items by category
foreach ($cartItems as $item) {
    $category = $item['category'] ?? '';
    if (is_array($category)) {
        $category = !empty($category) ? reset($category) : '';
    }
    $category = (string)$category;
    
    $quantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
    
    switch ($category) {
        case 'Meat':
            $meatCount += $quantity;
            break;
        case 'Fruits':
            $fruitCount += $quantity;
            break;
        case 'Vegetables':
            $vegetableCount += $quantity;
            break;
    }
}

echo "<h2>Category Counts:</h2>";
echo "<ul>";
echo "<li>Meat items: $meatCount</li>";
echo "<li>Fruit items: $fruitCount</li>";
echo "<li>Vegetable items: $vegetableCount</li>";
echo "</ul>";

// Check if we have the required items
$promotionApplied = false;
$originalTotal = 0;
$promotionPrice = 10.00;

if ($meatCount >= 1 && $fruitCount >= 2 && $vegetableCount >= 2) {
    echo "<h2 style='color:green'>Promotion conditions met!</h2>";
    
    // Mark items as part of the promotion
    $meatNeeded = 1;
    $fruitNeeded = 2;
    $vegetableNeeded = 2;
    $promotionalItems = [];
    $promotionalIds = [];
    
    // Second pass: mark items as part of the promotion
    foreach ($cartItems as &$item) {
        $category = $item['category'] ?? '';
        if (is_array($category)) {
            $category = !empty($category) ? reset($category) : '';
        }
        $category = (string)$category;
        
        $quantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
        
        if ($category === 'Meat' && $meatNeeded > 0) {
            $useQuantity = min($quantity, $meatNeeded);
            $meatNeeded -= $useQuantity;
            
            // Mark this item as part of the promotion
            $item['in_promotion'] = true;
            $promotionalIds[] = $item['id'];
            $promotionalItems[] = $item;
            
            // Add to original total
            $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
            $originalTotal += $itemPrice * $useQuantity;
            
            echo "<p>Added meat item: {$item['name']} to promotion</p>";
        }
        else if ($category === 'Fruits' && $fruitNeeded > 0) {
            $useQuantity = min($quantity, $fruitNeeded);
            $fruitNeeded -= $useQuantity;
            
            // Mark this item as part of the promotion
            $item['in_promotion'] = true;
            $promotionalIds[] = $item['id'];
            $promotionalItems[] = $item;
            
            // Add to original total
            $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
            $originalTotal += $itemPrice * $useQuantity;
            
            echo "<p>Added fruit item: {$item['name']} to promotion</p>";
        }
        else if ($category === 'Vegetables' && $vegetableNeeded > 0) {
            $useQuantity = min($quantity, $vegetableNeeded);
            $vegetableNeeded -= $useQuantity;
            
            // Mark this item as part of the promotion
            $item['in_promotion'] = true;
            $promotionalIds[] = $item['id'];
            $promotionalItems[] = $item;
            
            // Add to original total
            $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
            $originalTotal += $itemPrice * $useQuantity;
            
            echo "<p>Added vegetable item: {$item['name']} to promotion</p>";
        }
    }
    
    // Calculate the discount
    $discount = $originalTotal - $promotionPrice;
    
    // Only apply if there's a benefit
    if ($discount <= 0) {
        echo "<p>No discount would be applied because original price ($originalTotal) <= promotion price ($promotionPrice)</p>";
    } else {
        $promotionApplied = true;
        echo "<p style='color:green'>Promotion applied! Original total: \$$originalTotal, Promotion price: \$$promotionPrice, Discount: \$$discount</p>";
        
        // Calculate the new cart total
        $cartTotal = 0;
        foreach ($cartItems as $item) {
            $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
            $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
            $cartTotal += $itemPrice * $itemQuantity;
        }
        
        $newTotal = $cartTotal - $discount;
        echo "<p>Original cart total: \$$cartTotal, New total with discount: \$$newTotal</p>";
        
        // Apply the promotion to the session
        $_SESSION['cart']['items'] = $cartItems;
        $_SESSION['cart']['total'] = $newTotal;
        $_SESSION['cart']['promotion_applied'] = true;
        $_SESSION['cart']['promotion_discount'] = $discount;
        $_SESSION['cart']['promotion_price'] = $promotionPrice;
        $_SESSION['cart']['original_total'] = $originalTotal;
    }
} else {
    echo "<h2>Promotion conditions NOT met.</h2>";
}

echo "<div style='margin-top: 20px;'>";
echo "<a href='/cart' style='padding: 10px 20px; background-color: blue; color: white; text-decoration: none;'>Return to Cart</a>";
echo "</div>";
?> 