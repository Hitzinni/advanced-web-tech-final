<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Newsletter Model
 * Handles database operations related to newsletter subscriptions.
 */
class Newsletter extends BaseModel
{
    /** @var string The name of the database table used for subscribers. */
    protected $table = 'newsletter_subscribers';
    
    /**
     * Adds a new email address to the newsletter subscription list or reactivates an existing unsubscribed email.
     * Uses `ON DUPLICATE KEY UPDATE` to handle existing emails gracefully.
     * If an email exists but is marked as 'unsubscribed', it updates the status to 'active' and resets the subscribed_at timestamp.
     * If an email exists and is already 'active', it doesn't change the subscribed_at time but ensures status remains 'active'.
     * 
     * @param string $email The email address to subscribe.
     * @return bool True if the subscription was successful (or email was already active), false on database error.
     */
    public function subscribe(string $email): bool
    {
        try {
            // Prepare the SQL statement for insertion or update
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (email, subscribed_at) -- Columns to insert
                VALUES (:email, NOW()) -- Values for new subscription
                ON DUPLICATE KEY UPDATE -- Action if email (the unique key) already exists
                    subscribed_at = CASE 
                        WHEN status = 'unsubscribed' THEN NOW() -- If they unsubscribed, reset subscribe date
                        ELSE subscribed_at -- Otherwise, keep the original subscribe date
                    END,
                    status = 'active' -- Always ensure the status is active on subscribe/resubscribe
            ");
            
            // Execute the statement with the provided email
            $stmt->execute([
                'email' => $email
            ]);
            
            // Return true assuming success if no exception was thrown
            return true;
        } catch (\PDOException $e) {
            // Log any database errors
            error_log("Error adding newsletter subscriber: " . $e->getMessage());
            // Return false indicating failure
            return false;
        }
    }
} 