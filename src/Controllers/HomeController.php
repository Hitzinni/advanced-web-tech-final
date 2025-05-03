<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;
use App\Models\Review;

class HomeController
{
    private $reviewModel;
    
    public function __construct()
    {
        $this->reviewModel = new Review();
    }
    
    public function index(): void
    {
        $pageTitle = 'Welcome to Our Grocery Store';
        $metaDescription = 'Browse and order fresh vegetables and meats from our online grocery store.';
        
        // Get recent reviews to display on the homepage
        $reviews = $this->reviewModel->getRecentWithUserInfo(5);
        
        View::output('home', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'reviews' => $reviews
        ]);
    }
} 