<?php
declare(strict_types=1);

namespace App\Models;

/**
 * User Model
 * Handles database operations related to users, such as creation, retrieval, and updates.
 */
class User extends BaseModel
{
    /**
     * Creates a new user record in the database.
     * 
     * @param string $name User's full name.
     * @param string $phone User's phone number.
     * @param string $email User's email address.
     * @param string $passwordHash Hashed password for the user.
     * @return int The ID of the newly created user.
     * @throws \PDOException If the database insertion fails.
     */
    public function create(string $name, string $phone, string $email, string $passwordHash): int
    {
        try {
            // Log registration attempt for debugging
            error_log("User registration attempt - Email: {$email}, Name: {$name}, Phone: {$phone}");
            
            // SQL query to insert a new user
            $sql = "INSERT INTO user (name, phone, email, password_hash) VALUES (?, ?, ?, ?)";
            error_log("SQL Query: {$sql}"); // Log the SQL query
            
            // Prepare the SQL statement
            $stmt = $this->db->prepare($sql);
            
            // Log parameters being passed (excluding the hash itself for security)
            error_log("Parameters: " . json_encode([
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'password_hash_length' => strlen($passwordHash)
            ]));
            
            // Execute the query with provided parameters
            $result = $stmt->execute([$name, $phone, $email, $passwordHash]);
            
            // Check if execution failed
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("SQL Error: " . json_encode($errorInfo)); // Log detailed SQL error
                // Throw an exception if the query fails
                throw new \PDOException("Failed to insert user: " . ($errorInfo[2] ?? 'Unknown error'));
            }
            
            // Get the ID of the newly inserted user
            $userId = (int)$this->db->lastInsertId();
            
            // Log successful registration
            error_log("User registration successful - ID: {$userId}, Email: {$email}");
            
            // Return the new user ID
            return $userId;
        } catch (\PDOException $e) {
            // Log the database error if something goes wrong
            error_log("User registration failed: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Re-throw the exception to be handled by the caller
            throw $e;
        }
    }
    
    /**
     * Finds a user by their email address.
     * 
     * @param string $email The email address to search for.
     * @return array|null An associative array of the user data if found, otherwise null.
     */
    public function findByEmail(string $email)
    {
        // SQL query to select a user by email
        $sql = "SELECT * FROM user WHERE email = ? LIMIT 1";
        // Prepare and execute the statement
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        
        // Fetch the user data
        $user = $stmt->fetch();
        // Return the user array or null if not found
        return $user ?: null;
    }
    
    /**
     * Finds a user by their ID.
     * 
     * @param int $id The user ID to search for.
     * @return array|null An associative array of the user data if found, otherwise null.
     */
    public function findById(int $id)
    {
        // SQL query to select a user by ID
        $sql = "SELECT * FROM user WHERE id = ? LIMIT 1";
        // Prepare and execute the statement
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        // Fetch the user data
        $user = $stmt->fetch();
        // Return the user array or null if not found
        return $user ?: null;
    }
    
    /**
     * Checks if an email address is already registered.
     * 
     * @param string $email The email address to check.
     * @return bool True if the email is taken, false otherwise.
     */
    public function isEmailTaken(string $email): bool
    {
        // SQL query to check for the existence of an email (fetches 1 if exists)
        $sql = "SELECT 1 FROM user WHERE email = ? LIMIT 1";
        // Prepare and execute the statement
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        
        // Return true if a row was found (email exists), false otherwise
        return (bool)$stmt->fetch();
    }
    
    /**
     * Updates the password hash for a specific user.
     * 
     * @param int $userId The ID of the user whose password needs updating.
     * @param string $passwordHash The new hashed password.
     * @return bool True if the update was successful, false otherwise.
     * @throws \PDOException If the database update fails.
     */
    public function updatePassword(int $userId, string $passwordHash): bool
    {
        try {
            // Log password update attempt
            error_log("Password update attempt for user ID: {$userId}");
            
            // SQL query to update the password hash for a user
            $sql = "UPDATE user SET password_hash = ? WHERE id = ?";
            // Prepare the statement
            $stmt = $this->db->prepare($sql);
            // Execute the update
            $result = $stmt->execute([$passwordHash, $userId]);
            
            // Check if execution failed
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("SQL Error during password update: " . json_encode($errorInfo)); // Log detailed SQL error
                // Throw an exception if the query fails
                throw new \PDOException("Failed to update password: " . ($errorInfo[2] ?? 'Unknown error'));
            }
            
            // Log successful password update
            error_log("Password updated successfully for user ID: {$userId}");
            // Return true indicating success
            return true;
        } catch (\PDOException $e) {
            // Log the database error if something goes wrong
            error_log("Password update failed: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Re-throw the exception to be handled by the caller
            throw $e;
        }
    }
} 