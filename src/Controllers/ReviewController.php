<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Review;
use App\Helpers\Validators;
use App\Helpers\View;

class ReviewController
{
    private $reviewModel;
    
    public function __construct()
    {
        $this->reviewModel = new Review();
    }
    
    /**
     * Show the review form
     */
    public function showReviewForm()
    {
        // Check if the user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You need to be logged in to submit a review.";
            header('Location: ' . View::url('login'));
            exit;
        }
        
        // Fetch user's existing reviews
        $userReviews = $this->reviewModel->getByUserId($_SESSION['user_id']);
        
        // Render view using View helper
        View::output('review-form', [
            'pageTitle' => "Write a Review",
            'metaDescription' => "Share your experience with our grocery store",
            'userReviews' => $userReviews
        ]);
    }
    
    /**
     * Process submitted review form
     */
    public function submitReview()
    {
        // Check if the user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You need to be logged in to submit a review.";
            header('Location: ' . View::url('login'));
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate input
            $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
            $content = isset($_POST['content']) ? trim($_POST['content']) : '';
            
            $errors = [];
            
            // Validate rating
            if ($rating < 1 || $rating > 5) {
                $errors[] = "Rating must be between 1 and 5 stars.";
            }
            
            // Validate content
            if (empty($content)) {
                $errors[] = "Review content cannot be empty.";
            } elseif (strlen($content) < 10) {
                $errors[] = "Review content must be at least 10 characters.";
            } elseif (strlen($content) > 1000) {
                $errors[] = "Review content cannot exceed 1000 characters.";
            }
            
            if (empty($errors)) {
                // Save the review
                $result = $this->reviewModel->create($_SESSION['user_id'], $rating, $content);
                
                if ($result) {
                    $_SESSION['success'] = "Your review has been submitted. Thank you for your feedback!";
                    header('Location: ' . View::url('home'));
                    exit;
                } else {
                    $_SESSION['error'] = "There was an error submitting your review. Please try again.";
                    header('Location: ' . View::url('review'));
                    exit;
                }
            } else {
                // Set errors and redirect back to form
                $_SESSION['errors'] = $errors;
                $_SESSION['form_data'] = [
                    'rating' => $rating,
                    'content' => $content
                ];
                header('Location: ' . View::url('review'));
                exit;
            }
        } else {
            // Not a POST request
            header('Location: ' . View::url('review'));
            exit;
        }
    }
    
    /**
     * Delete a review
     */
    public function deleteReview()
    {
        // Check if the user is logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "You need to be logged in to delete a review.";
            header('Location: ' . View::url('login'));
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
            $reviewId = (int)$_POST['review_id'];
            
            // Delete the review (method ensures it belongs to the user)
            $result = $this->reviewModel->delete($reviewId, $_SESSION['user_id']);
            
            if ($result) {
                $_SESSION['success'] = "Your review has been deleted.";
            } else {
                $_SESSION['error'] = "There was an error deleting your review.";
            }
        }
        
        // Redirect back to reviews page
        header('Location: ' . View::url('review'));
        exit;
    }
} 