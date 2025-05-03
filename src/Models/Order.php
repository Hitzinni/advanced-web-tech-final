<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Order Model
 * Handles database operations related to orders (both legacy and new formats).
 */
class Order extends BaseModel
{
    /**
     * Legacy create method for single product orders.
     * Inserts a record into the old `order` table.
     * 
     * @param int $userId User ID placing the order.
     * @param int $productId Product ID being ordered.
     * @param float $price Price of the product at the time of order.
     * @return int The ID of the newly created legacy order.
     */
    public function create(int $userId, int $productId, float $price): int
    {
        // SQL to insert into the legacy `order` table
        $sql = "INSERT INTO `order` (user_id, product_id, price_at_order) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $productId, $price]);
        
        // Return the ID of the inserted row
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Creates a new order with multiple items in the modern `orders` and `order_items` tables.
     * Uses a transaction to ensure atomicity.
     * 
     * @param array $orderData Associative array containing order details:
     *  - user_id: ID of the user placing the order.
     *  - total_amount: Total cost including shipping.
     *  - subtotal: Cost of items before shipping.
     *  - shipping_fee: Cost of shipping.
     *  - shipping_address: Full shipping address string.
     *  - payment_method: Method of payment (e.g., 'Credit Card', 'Cash on Delivery').
     *  - items: Array of items, each with 'id', 'name', 'price', 'quantity'.
     * @return int The ID of the newly created order in the `orders` table.
     * @throws \PDOException If the transaction fails.
     */
    public function createOrder(array $orderData): int
    {
        try {
            // Start a database transaction
            $this->db->beginTransaction();
            
            // Insert the main order record into the `orders` table
            $sql = "INSERT INTO `orders` 
                    (user_id, total_amount, subtotal, shipping_fee, shipping_address, payment_method, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')"; // Default status is 'pending'
            
            // Prepare and execute the main order insertion
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $orderData['user_id'],
                $orderData['total_amount'],
                $orderData['subtotal'],
                $orderData['shipping_fee'],
                $orderData['shipping_address'],
                $orderData['payment_method']
            ]);
            
            // Get the ID of the newly created order
            $orderId = (int)$this->db->lastInsertId();
            
            // Insert each associated item into the `order_items` table
            if (isset($orderData['items']) && !empty($orderData['items'])) {
                $itemSql = "INSERT INTO `order_items` 
                            (order_id, product_id, product_name, price, quantity) 
                            VALUES (?, ?, ?, ?, ?)";
                
                // Prepare the item insertion statement once
                $itemStmt = $this->db->prepare($itemSql);
                
                // Loop through items and execute the prepared statement
                foreach ($orderData['items'] as $item) {
                    $itemStmt->execute([
                        $orderId,          // Link to the main order
                        $item['id'],         // Product ID
                        $item['name'],       // Product Name (at time of order)
                        $item['price'],      // Price (at time of order)
                        $item['quantity']    // Quantity ordered
                    ]);
                }
            }
            
            // Commit the transaction if all insertions were successful
            $this->db->commit();
            // Return the ID of the main order record
            return $orderId;
            
        } catch (\PDOException $e) {
            // If any error occurs, roll back the transaction
            $this->db->rollBack();
            // Log the error
            error_log("Error creating order: " . $e->getMessage());
            // Re-throw the exception
            throw $e;
        }
    }
    
    /**
     * Retrieves an order by its ID, checking both new and legacy tables.
     * If found in the new `orders` table, it also fetches associated items.
     * 
     * @param int $id The ID of the order to retrieve.
     * @return array|null An associative array of the order data (with items if new format), or null if not found.
     */
    public function getById(int $id)
    {
        try {
            // Attempt to find the order in the new `orders` table first
            $sql = "SELECT o.*, o.shipping_address, o.payment_method, u.name as user_name, u.email, u.phone
                   FROM `orders` o
                   JOIN user u ON o.user_id = u.id
                   WHERE o.id = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $order = $stmt->fetch();
            
            // If found in the new table
            if ($order) {
                // Fetch associated items from `order_items`
                $itemsSql = "SELECT oi.*, p.category, p.image_url 
                            FROM `order_items` oi
                            JOIN product p ON oi.product_id = p.id
                            WHERE oi.order_id = ?";
                $itemsStmt = $this->db->prepare($itemsSql);
                $itemsStmt->execute([$id]);
                $order['items'] = $itemsStmt->fetchAll();
                
                // Add a flag indicating this uses the new multi-item format
                $order['is_new_format'] = true;
                
                // Return the order data with items
                return $order;
            }
            
            // If not found in the new table, fall back to the legacy `order` table
            $sql = "SELECT o.*, p.name as product_name, p.category, p.image_url 
                    FROM `order` o
                    JOIN product p ON o.product_id = p.id
                    WHERE o.id = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            
            // Fetch the legacy order data
            $order = $stmt->fetch();
            if ($order) {
                // Add a flag indicating this uses the legacy format
                $order['is_new_format'] = false;
            }
            
            // Return the legacy order data or null if not found in either table
            return $order ?: null;
        } catch (\PDOException $e) {
            // Log any database errors during fetching
            error_log("Error fetching order: " . $e->getMessage());
            // Return null on error
            return null;
        }
    }
    
    /**
     * Retrieves detailed information for a legacy order by its ID.
     * Joins with product and user tables.
     * 
     * @param int $id The ID of the legacy order.
     * @return array|null Detailed order information or null if not found.
     */
    public function getDetailedById(int $id)
    {
        // SQL query to get detailed info for a specific legacy order
        $sql = "SELECT 
                  o.id as order_id, 
                  o.ordered_at,
                  o.price_at_order,
                  p.id as product_id, 
                  p.name as product_name, 
                  p.category, 
                  p.price as product_price,
                  p.image_url,
                  u.id as user_id,
                  u.name as user_name,
                  u.email,
                  u.phone
                FROM `order` o
                JOIN product p ON o.product_id = p.id
                JOIN user u ON o.user_id = u.id
                WHERE o.id = ? LIMIT 1";
        // Prepare and execute
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        // Fetch and return the result
        $order = $stmt->fetch();
        return $order ?: null;
    }
    
    /**
     * Retrieves all orders for a specific user, combining new and legacy formats.
     * Orders are sorted by date, newest first.
     * 
     * @param int $userId The ID of the user whose orders are to be retrieved.
     * @return array An array of all orders for the user, sorted by date.
     */
    public function getByUserId(int $userId): array
    {
        try {
            $result = [];
            
            // Check if the new `orders` table exists
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'orders'");
            $ordersTableExists = $tableCheck->rowCount() > 0;
            
            // If the new table exists, fetch orders from it
            if ($ordersTableExists) {
                // SQL to get orders from the new structure, including item count
                $sql = "SELECT o.*, COUNT(oi.id) as item_count
                       FROM `orders` o
                       LEFT JOIN order_items oi ON o.id = oi.order_id
                       WHERE o.user_id = ?
                       GROUP BY o.id
                       ORDER BY o.ordered_at DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$userId]);
                $newOrders = $stmt->fetchAll();
                
                // Mark these orders with the 'is_new_format' flag
                foreach ($newOrders as &$order) {
                    $order['is_new_format'] = true;
                }
                
                // Add new orders to the result array
                $result = array_merge($result, $newOrders);
            }
            
            // Fetch orders from the legacy `order` table
            $sql = "SELECT o.*, p.name as product_name, p.category, p.image_url 
                    FROM `order` o
                    JOIN product p ON o.product_id = p.id
                    WHERE o.user_id = ?
                    ORDER BY o.ordered_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            $legacyOrders = $stmt->fetchAll();
            
            // Mark these orders with the 'is_new_format' flag set to false
            foreach ($legacyOrders as &$order) {
                $order['is_new_format'] = false;
            }
            
            // Combine both new and legacy orders into one array
            $result = array_merge($result, $legacyOrders);
            
            // Sort the combined results by order date (descending)
            usort($result, function($a, $b) {
                return strtotime($b['ordered_at']) - strtotime($a['ordered_at']);
            });
            
            // Return the sorted list of all orders
            return $result;
        } catch (\PDOException $e) {
            // Log errors encountered during fetching
            error_log('Order Model - getByUserId - Error: ' . $e->getMessage());
            // Return an empty array on error
            return [];
        }
    }
    
    /**
     * Updates the status of an order in the new `orders` table.
     * Also updates the `updated_at` timestamp.
     * 
     * @param int $orderId The ID of the order (in the `orders` table) to update.
     * @param string $status The new status value (e.g., 'processing', 'shipped', 'delivered').
     * @return bool True if the update affected at least one row, false otherwise or on error.
     */
    public function updateStatus(int $orderId, string $status): bool
    {
        try {
            // SQL to update status and timestamp in the `orders` table
            // Note: This only targets the new orders table
            $sql = "UPDATE `orders` SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$status, $orderId]);
            
            // Return true only if the execute succeeded AND at least one row was changed
            return $result && $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            // Log any database errors during the update
            error_log("Error updating order status: " . $e->getMessage());
            // Return false on error
            return false;
        }
    }
} 