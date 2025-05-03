<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Cart Model
 * Handles database operations related to shopping carts and their items.
 */
class CartModel
{
    /** @var \PDO Database connection instance. */
    private $db;
    
    /**
     * Constructor
     * 
     * @param \PDO $db An active PDO database connection.
     */
    public function __construct(\PDO $db)
    {
        // Store the provided database connection
        $this->db = $db;
    }
    
    /**
     * Gets the cart ID for a given user. If no cart exists, creates a new one.
     * 
     * @param int $userId The ID of the user.
     * @return int The ID of the user's cart.
     */
    public function getOrCreateCart(int $userId): int
    {
        // Attempt to find an existing cart for the user
        $stmt = $this->db->prepare('SELECT id FROM cart WHERE user_id = ?');
        $stmt->execute([$userId]);
        $cart = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // If a cart is found, return its ID
        if ($cart) {
            return (int)$cart['id'];
        }
        
        // If no cart exists, create a new one
        $stmt = $this->db->prepare('INSERT INTO cart (user_id) VALUES (?)');
        $stmt->execute([$userId]);
        
        // Return the ID of the newly created cart
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Adds a product to a specific cart. If the item already exists, updates its quantity.
     * 
     * @param int $cartId The ID of the cart.
     * @param int $productId The ID of the product to add.
     * @param int $quantity The quantity of the product to add (defaults to 1).
     * @return bool True on success, false on failure.
     */
    public function addItem(int $cartId, int $productId, int $quantity = 1): bool
    {
        try {
            // Check if the item already exists in the cart
            $stmt = $this->db->prepare('SELECT id, quantity FROM cart_item WHERE cart_id = ? AND product_id = ?');
            $stmt->execute([$cartId, $productId]);
            $item = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($item) {
                // If the item exists, update its quantity
                $newQuantity = (int)$item['quantity'] + $quantity;
                $stmt = $this->db->prepare('UPDATE cart_item SET quantity = ? WHERE id = ?');
                return $stmt->execute([$newQuantity, $item['id']]);
            } else {
                // If the item does not exist, insert it as a new cart item
                $stmt = $this->db->prepare('INSERT INTO cart_item (cart_id, product_id, quantity) VALUES (?, ?, ?)');
                return $stmt->execute([$cartId, $productId, $quantity]);
            }
        } catch (\PDOException $e) {
            // Log database errors
            error_log("Error adding item to cart: " . $e->getMessage());
            // Return false on error
            return false;
        }
    }
    
    /**
     * Updates the quantity of a specific item in the cart.
     * If quantity is 0 or less, the item is removed.
     * 
     * @param int $cartItemId The ID of the cart item to update.
     * @param int $quantity The new quantity for the item.
     * @return bool True on success, false on failure.
     */
    public function updateItemQuantity(int $cartItemId, int $quantity): bool
    {
        try {
            // If the requested quantity is zero or less, remove the item instead
            if ($quantity <= 0) {
                return $this->removeItem($cartItemId);
            }
            
            // Update the quantity for the specified cart item ID
            $stmt = $this->db->prepare('UPDATE cart_item SET quantity = ? WHERE id = ?');
            return $stmt->execute([$quantity, $cartItemId]);
        } catch (\PDOException $e) {
            // Log database errors
            error_log("Error updating cart item quantity: " . $e->getMessage());
            // Return false on error
            return false;
        }
    }
    
    /**
     * Removes a specific item from the cart by its cart item ID.
     * 
     * @param int $cartItemId The ID of the cart item to remove.
     * @return bool True on success, false on failure.
     */
    public function removeItem(int $cartItemId): bool
    {
        try {
            // Delete the cart item with the specified ID
            $stmt = $this->db->prepare('DELETE FROM cart_item WHERE id = ?');
            return $stmt->execute([$cartItemId]);
        } catch (\PDOException $e) {
            // Log database errors
            error_log("Error removing item from cart: " . $e->getMessage());
            // Return false on error
            return false;
        }
    }
    
    /**
     * Retrieves all items currently in a user's cart, joined with product details.
     * 
     * @param int $userId The ID of the user whose cart items are to be retrieved.
     * @return array An array of associative arrays, each representing a cart item with product details.
     */
    public function getCartItems(int $userId): array
    {
        try {
            // SQL query to join cart, cart_item, and product tables
            $stmt = $this->db->prepare('
                SELECT ci.id, ci.quantity, p.id as product_id, p.name, p.price, p.image_url, p.category
                FROM cart c
                JOIN cart_item ci ON c.id = ci.cart_id
                JOIN product p ON ci.product_id = p.id
                WHERE c.user_id = ?
            ');
            $stmt->execute([$userId]);
            // Fetch all matching items
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Log database errors
            error_log("Error getting cart items: " . $e->getMessage());
            // Return an empty array on error
            return [];
        }
    }
    
    /**
     * Calculates the total number of individual items (sum of quantities) in a user's cart.
     * 
     * @param int $userId The ID of the user.
     * @return int The total count of items in the cart.
     */
    public function getCartItemCount(int $userId): int
    {
        try {
            // SQL query to sum the quantities of all items in the user's cart
            $stmt = $this->db->prepare('
                SELECT SUM(ci.quantity) as count
                FROM cart c
                JOIN cart_item ci ON c.id = ci.cart_id
                WHERE c.user_id = ?
            ');
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            // Return the sum (count) or 0 if the cart is empty or an error occurs
            return (int)($result['count'] ?? 0);
        } catch (\PDOException $e) {
            // Log database errors
            error_log("Error getting cart item count: " . $e->getMessage());
            // Return 0 on error
            return 0;
        }
    }
    
    /**
     * Calculates the total monetary value of all items in a user's cart.
     * 
     * @param int $userId The ID of the user.
     * @return float The total value of the cart items.
     */
    public function getCartTotal(int $userId): float
    {
        try {
            // SQL query to calculate the sum of (quantity * price) for all items
            $stmt = $this->db->prepare('
                SELECT SUM(ci.quantity * p.price) as total
                FROM cart c
                JOIN cart_item ci ON c.id = ci.cart_id
                JOIN product p ON ci.product_id = p.id
                WHERE c.user_id = ?
            ');
            $stmt->execute([$userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            // Return the total value or 0.0 if the cart is empty or an error occurs
            return (float)($result['total'] ?? 0);
        } catch (\PDOException $e) {
            // Log database errors
            error_log("Error calculating cart total: " . $e->getMessage());
            // Return 0.0 on error
            return 0.0;
        }
    }
    
    /**
     * Removes all items from a user's cart.
     * 
     * @param int $userId The ID of the user whose cart should be cleared.
     * @return bool True on success, false on failure.
     */
    public function clearCart(int $userId): bool
    {
        try {
            // SQL query to delete all cart items associated with the user's cart
            $stmt = $this->db->prepare('
                DELETE ci FROM cart_item ci
                JOIN cart c ON ci.cart_id = c.id
                WHERE c.user_id = ?
            ');
            // Execute the deletion
            return $stmt->execute([$userId]);
        } catch (\PDOException $e) {
            // Log database errors
            error_log("Error clearing cart: " . $e->getMessage());
            // Return false on error
            return false;
        }
    }
} 