<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Newsletter;
use App\Helpers\View;

class NewsletterController
{
    private $newsletterModel;
    
    public function __construct()
    {
        $this->newsletterModel = new Newsletter();
    }
    
    /**
     * Process newsletter subscription
     */
    public function subscribe()
    {
        // Check if the request is a POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['newsletter_error'] = "Invalid request method.";
            header('Location: ' . View::url('home'));
            exit;
        }
        
        // Get and validate email
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        
        if (empty($email)) {
            $_SESSION['newsletter_error'] = "Please provide an email address.";
            header('Location: ' . View::url('home'));
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['newsletter_error'] = "Please provide a valid email address.";
            header('Location: ' . View::url('home'));
            exit;
        }
        
        // Save the subscription
        $result = $this->newsletterModel->subscribe($email);
        
        if ($result) {
            $_SESSION['newsletter_success'] = "Thank you for subscribing to our newsletter!";
        } else {
            $_SESSION['newsletter_error'] = "Something went wrong. Please try again later.";
        }
        
        // Redirect back to the home page
        header('Location: ' . View::url('home'));
        exit;
    }
} 