<?php
namespace App\Controllers;

class ErrorController {
    public function notFound() {
        // Set page title and meta description
        $pageTitle = "Page Not Found";
        $metaDescription = "The page you were looking for could not be found.";
        
        $content = <<<HTML
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-container py-5">
                <h1 class="display-1 text-danger">404</h1>
                <h2 class="mb-4">Page Not Found</h2>
                <p class="lead mb-4">The page you were looking for could not be found. It might have been removed, renamed, or does not exist.</p>
                <a href="/" class="btn btn-primary">
                    <i class="bi bi-house-door me-2"></i>Return to Home
                </a>
            </div>
        </div>
    </div>
</div>
HTML;

        // Include the layout
        require_once BASE_PATH . '/src/Helpers/View.php';
        \App\Helpers\View::render('layout', [
            'content' => $content,
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription
        ]);
    }
} 