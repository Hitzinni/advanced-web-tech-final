<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Shipping Address Model
 * Handles database operations related to user shipping addresses.
 */
class ShippingAddress extends BaseModel
{
    /**
     * Creates a new shipping address record for a specified user.
     * If the new address is marked as default, it first unsets the default flag on any other addresses for that user.
     * 
     * @param array $addressData Associative array containing address details:
     *   - user_id: (int) ID of the user this address belongs to.
     *   - first_name: (string) Recipient's first name.
     *   - last_name: (string) Recipient's last name.
     *   - address: (string) Street address line.
     *   - city: (string) City name.
     *   - state: (string) State, county, or region.
     *   - zip: (string) Postal code.
     *   - phone: (string) Contact phone number.
     *   - is_default: (bool|int, optional) Whether this address should be the default (1 for yes, 0 for no). Defaults to 0.
     * @return int The ID of the newly created address record.
     * @throws \PDOException If the database insertion fails.
     */
    public function create(array $addressData): int
    {
        try {
            // If 'is_default' is set and true in the input data,
            // call a helper method to remove the default flag from other addresses for this user.
            if (isset($addressData['is_default']) && $addressData['is_default']) {
                $this->unsetDefaultAddress((int)$addressData['user_id']);
            }
            
            // SQL query to insert the new shipping address
            $sql = "INSERT INTO shipping_address 
                    (user_id, first_name, last_name, address, city, state, zip, phone, is_default) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Prepare the SQL statement
            $stmt = $this->db->prepare($sql);
            // Execute the statement with the provided address data.
            // Use null coalescing operator for is_default to ensure it's 0 if not provided.
            $stmt->execute([
                $addressData['user_id'],
                $addressData['first_name'],
                $addressData['last_name'],
                $addressData['address'],
                $addressData['city'],
                $addressData['state'],
                $addressData['zip'],
                $addressData['phone'],
                $addressData['is_default'] ?? 0
            ]);
            
            // Return the ID of the newly inserted address row
            return (int)$this->db->lastInsertId();
        } catch (\PDOException $e) {
            // Log any database errors that occur during creation
            error_log("Error creating shipping address: " . $e->getMessage());
            // Re-throw the exception for the controller to handle
            throw $e;
        }
    }
    
    /**
     * Retrieves all shipping addresses associated with a given user ID.
     * Orders the addresses with the default address first, then by most recently updated.
     * 
     * @param int $userId The ID of the user whose addresses are to be fetched.
     * @return array An array of associative arrays, each representing a shipping address. Returns an empty array if none are found or on error.
     */
    public function getByUserId(int $userId): array
    {
        try {
            // SQL query to select all addresses for the user, ordered by default status and update time
            $sql = "SELECT * FROM shipping_address WHERE user_id = ? ORDER BY is_default DESC, updated_at DESC";
            
            // Prepare and execute the statement
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            
            // Fetch all results. Use null coalescing to return empty array if fetchAll returns false.
            return $stmt->fetchAll() ?: [];
        } catch (\PDOException $e) {
            // Log any database errors during fetching
            error_log("Error fetching shipping addresses: " . $e->getMessage());
            // Return an empty array on error
            return [];
        }
    }
    
    /**
     * Retrieves the default shipping address for a given user ID.
     * 
     * @param int $userId The ID of the user.
     * @return array|null An associative array representing the default address, or null if no default address is set or on error.
     */
    public function getDefaultAddress(int $userId)
    {
        try {
            // SQL query to select the address marked as default for the user
            $sql = "SELECT * FROM shipping_address WHERE user_id = ? AND is_default = 1 LIMIT 1";
            
            // Prepare and execute
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            
            // Fetch the result
            $address = $stmt->fetch();
            // Return the address array or null if not found
            return $address ?: null;
        } catch (\PDOException $e) {
            // Log database errors
            error_log("Error fetching default address: " . $e->getMessage());
            // Return null on error
            return null;
        }
    }
    
    /**
     * Retrieves a specific shipping address by its ID, verifying ownership by user ID.
     * 
     * @param int $id The ID of the shipping address record.
     * @param int $userId The ID of the user who should own this address.
     * @return array|null An associative array of the address data if found and owned by the user, null otherwise or on error.
     */
    public function getById(int $id, int $userId)
    {
        try {
            // SQL query to select a specific address by its ID and user ID
            $sql = "SELECT * FROM shipping_address WHERE id = ? AND user_id = ? LIMIT 1";
            
            // Prepare and execute
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id, $userId]);
            
            // Fetch the result
            $address = $stmt->fetch();
            // Return the address array or null if not found/not owned
            return $address ?: null;
        } catch (\PDOException $e) {
            // Log database errors
            error_log("Error fetching shipping address: " . $e->getMessage());
            // Return null on error
            return null;
        }
    }
    
    /**
     * Updates an existing shipping address.
     * If the address is being set as default, it first unsets the default flag on other addresses for the user.
     * 
     * @param int $id The ID of the address record to update.
     * @param array $addressData Associative array containing the updated address details (including user_id for verification).
     * @return bool True if the update was successful, false otherwise or on error.
     */
    public function update(int $id, array $addressData): bool
    {
        try {
            // If the update includes setting this address as default,
            // unset the default flag on any other addresses for the user first.
            if (isset($addressData['is_default']) && $addressData['is_default']) {
                $this->unsetDefaultAddress((int)$addressData['user_id']);
            }
            
            // SQL query to update the address details
            $sql = "UPDATE shipping_address SET 
                    first_name = ?, last_name = ?, address = ?, city = ?, 
                    state = ?, zip = ?, phone = ?, is_default = ? 
                    WHERE id = ? AND user_id = ?"; // Include user_id in WHERE for security
            
            // Prepare the statement
            $stmt = $this->db->prepare($sql);
            // Execute the update with the provided data
            return $stmt->execute([
                $addressData['first_name'],
                $addressData['last_name'],
                $addressData['address'],
                $addressData['city'],
                $addressData['state'],
                $addressData['zip'],
                $addressData['phone'],
                $addressData['is_default'] ?? 0, // Default to 0 if not provided
                $id,                                // Address ID for WHERE clause
                $addressData['user_id']            // User ID for WHERE clause
            ]);
        } catch (\PDOException $e) {
            // Log database errors during update
            error_log("Error updating shipping address: " . $e->getMessage());
            // Return false on error
            return false;
        }
    }
    
    /**
     * Deletes a specific shipping address, verifying ownership by user ID.
     * 
     * @param int $id The ID of the address record to delete.
     * @param int $userId The ID of the user who owns the address.
     * @return bool True if the deletion was successful, false otherwise or on error.
     */
    public function delete(int $id, int $userId): bool
    {
        try {
            // SQL query to delete the address, ensuring it belongs to the specified user
            $sql = "DELETE FROM shipping_address WHERE id = ? AND user_id = ?";
            
            // Prepare and execute
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id, $userId]);
        } catch (\PDOException $e) {
            // Log database errors during deletion
            error_log("Error deleting shipping address: " . $e->getMessage());
            // Return false on error
            return false;
        }
    }
    
    /**
     * Private helper method to set the `is_default` flag to 0 for all addresses of a specific user.
     * Called before setting a new default address.
     * 
     * @param int $userId The ID of the user whose addresses need updating.
     * @return bool True if the update was successful, false otherwise or on error.
     */
    private function unsetDefaultAddress(int $userId): bool
    {
        try {
            // SQL query to set is_default to 0 for all addresses belonging to the user
            $sql = "UPDATE shipping_address SET is_default = 0 WHERE user_id = ?";
            
            // Prepare and execute
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId]);
        } catch (\PDOException $e) {
            // Log database errors
            error_log("Error unsetting default addresses: " . $e->getMessage());
            // Return false on error
            return false;
        }
    }
} 