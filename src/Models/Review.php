<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Review Model
 * Handles database operations related to customer reviews.
 */
class Review extends BaseModel
{
    /** @var string The name of the database table for reviews. */
    protected $table = 'review';
    
    /**
     * Creates a new review record in the database.
     * 
     * @param int $userId The ID of the user submitting the review.
     * @param int $rating The star rating (e.g., 1-5).
     * @param string $content The text content of the review.
     * @return int|false The ID of the newly created review on success, or false on failure.
     */
    public function create(int $userId, int $rating, string $content)
    {
        try {
            // Prepare the SQL statement to insert a new review
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (user_id, rating, content)
                VALUES (:user_id, :rating, :content)
            ");
            
            // Execute the statement with the provided review data
            $stmt->execute([
                'user_id' => $userId,
                'rating' => $rating,
                'content' => $content
            ]);
            
            // Return the ID of the newly inserted review
            return (int)$this->db->lastInsertId();
        } catch (\PDOException $e) {
            // Log any database errors during insertion
            error_log("Error creating review: " . $e->getMessage());
            // Return false on error
            return false;
        }
    }
    
    /**
     * Retrieves a specified number of recent reviews, joining with user information (name).
     * Used typically for displaying recent reviews on the homepage.
     * 
     * @param int $limit The maximum number of reviews to retrieve (defaults to 5).
     * @return array An array of associative arrays, each representing a review with user name. Returns empty array on error.
     */
    public function getRecentWithUserInfo(int $limit = 5): array
    {
        try {
            // Prepare the SQL statement to select recent reviews and the reviewer's name
            $stmt = $this->db->prepare("
                SELECT r.id, r.rating, r.content, r.created_at, u.name as user_name -- Select review fields and user name
                FROM {$this->table} r -- From the review table (aliased as r)
                JOIN user u ON r.user_id = u.id -- Join with user table (aliased as u) on user ID
                ORDER BY r.created_at DESC -- Order by creation date, newest first
                LIMIT :limit -- Limit the number of results
            ");
            
            // Bind the limit parameter as an integer
            $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
            // Execute the query
            $stmt->execute();
            
            // Fetch all results as an associative array
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Log database errors
            error_log("Error fetching reviews: " . $e->getMessage());
            // Return an empty array on error
            return [];
        }
    }
    
    /**
     * Retrieves all reviews submitted by a specific user.
     * 
     * @param int $userId The ID of the user whose reviews are to be fetched.
     * @return array An array of associative arrays, each representing a review by the user. Returns empty array on error.
     */
    public function getByUserId(int $userId): array
    {
        try {
            // Prepare SQL to select reviews for a specific user ID
            $stmt = $this->db->prepare("
                SELECT id, rating, content, created_at
                FROM {$this->table}
                WHERE user_id = :user_id
                ORDER BY created_at DESC -- Order by newest first
            ");
            
            // Bind the user ID parameter
            $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
            // Execute the query
            $stmt->execute();
            
            // Fetch all matching reviews
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Log database errors
            error_log("Error fetching user reviews: " . $e->getMessage());
            // Return an empty array on error
            return [];
        }
    }
    
    /**
     * Deletes a specific review by its ID, but only if it belongs to the specified user ID.
     * 
     * @param int $reviewId The ID of the review to delete.
     * @param int $userId The ID of the user attempting to delete the review (for ownership verification).
     * @return bool True if the review was successfully deleted (i.e., found and owned by the user), false otherwise or on error.
     */
    public function delete(int $reviewId, int $userId): bool
    {
        try {
            // Prepare SQL to delete a review by its ID and the owner's user ID
            $stmt = $this->db->prepare("
                DELETE FROM {$this->table}
                WHERE id = :id AND user_id = :user_id
            ");
            
            // Execute the deletion query with named parameters
            $stmt->execute([
                'id' => $reviewId,
                'user_id' => $userId
            ]);
            
            // Return true if one or more rows were affected (meaning deletion occurred)
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            // Log database errors
            error_log("Error deleting review: " . $e->getMessage());
            // Return false on error
            return false;
        }
    }
} 