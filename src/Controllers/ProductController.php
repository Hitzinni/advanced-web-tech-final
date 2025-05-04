<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;
use App\Models\Product;
use App\Helpers\Validators;

class ProductController
{
    private $productModel;
    
    public function __construct()
    {
        error_log("ProductController::__construct - Controller instantiated");
        $this->productModel = new Product();
    }
    
    public function browse(): void
    {
        error_log("ProductController::browse - Method called");
        error_log("ProductController::browse - REQUEST_URI: " . $_SERVER['REQUEST_URI']);
        error_log("ProductController::browse - SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME']);
        error_log("ProductController::browse - Current route path: " . ($_GET['route'] ?? 'not set in GET'));
        
        // Log full GET parameters
        error_log("ProductController::browse - GET parameters: " . print_r($_GET, true));
        
        // Handle both old and new URL formats
        // Check if the 'category' parameter is appended directly to the route
        $routeParam = $_GET['route'] ?? '';
        $selectedCategory = '';
        
        // Parse ?route=products?category=xyz format
        if (strpos($routeParam, '?category=') !== false) {
            $parts = explode('?category=', $routeParam);
            if (count($parts) > 1) {
                $selectedCategory = urldecode($parts[1]);
            }
        } 
        // Standard &category= format
        else if (isset($_GET['category'])) {
            $selectedCategory = trim($_GET['category']);
        }
        
        $selectedProductId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
        
        error_log("ProductController::browse - Selected category: " . $selectedCategory);
        error_log("ProductController::browse - Selected product ID: " . ($selectedProductId ?? 'none'));
        
        // Validate category if one is selected
        if (!empty($selectedCategory) && !Validators::category($selectedCategory)) {
            error_log("ProductController::browse - Invalid category: " . $selectedCategory);
            $selectedCategory = '';
            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'text' => 'Invalid category selected.'
            ];
        }
        
        try {
            $categories = $this->productModel->getCategories();
            error_log("ProductController::browse - Retrieved " . count($categories) . " categories");
            
            // Fetch products based on selected category
            $products = !empty($selectedCategory) 
                ? $this->productModel->getByCategory($selectedCategory)
                : [];
                
            error_log("ProductController::browse - Retrieved " . count($products) . " products");
            
            // Store products in session for cart functionality
            $_SESSION['current_products'] = $products;
            
            $pageTitle = !empty($selectedCategory) 
                ? "Browse {$selectedCategory}" 
                : 'Browse Products';
                
            $metaDescription = !empty($selectedCategory)
                ? "Shop our selection of {$selectedCategory} products. Fresh and high-quality items for delivery."
                : 'Browse our selection of fresh vegetables and quality meats.';
                
            error_log("ProductController::browse - About to render browse view");
            
            View::output('browse', [
                'pageTitle' => $pageTitle,
                'metaDescription' => $metaDescription,
                'categories' => $categories,
                'products' => $products,
                'selectedCategory' => $selectedCategory,
                'selectedProductId' => $selectedProductId
            ]);
            
            error_log("ProductController::browse - View rendered successfully");
        } catch (\Exception $e) {
            error_log("ProductController::browse - Exception: " . $e->getMessage());
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'An error occurred while loading products.'
            ];
            header('Location: ' . View::url('home'));
            exit;
        }
    }
    
    /**
     * Display detailed view of a single product
     */
    public function detail(): void
    {
        error_log("ProductController::detail - Method called");
        
        // Get product ID from URL
        $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        error_log("ProductController::detail - Product ID: " . $productId);
        
        if ($productId <= 0) {
            // Redirect to browse page if no valid product ID
            error_log("ProductController::detail - Invalid product ID, redirecting to products page");
            header('Location: ' . View::url('products'));
            exit;
        }
        
        try {
            // Fetch product details
            $product = $this->productModel->getById($productId);
            
            if (!$product) {
                // If product not found, redirect to browse page
                error_log("ProductController::detail - Product not found, redirecting to products page");
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'Product not found.'
                ];
                header('Location: ' . View::url('products'));
                exit;
            }
            
            error_log("ProductController::detail - Product found: " . $product['name']);
            
            // Get all categories for related products
            $categories = $this->productModel->getCategories();
            
            // Get related products (same category, excluding current product)
            $relatedProducts = [];
            if (isset($product['category']) && $product['category']) {
                $categoryProducts = $this->productModel->getByCategory($product['category']);
                foreach ($categoryProducts as $relatedProduct) {
                    if ((int)$relatedProduct['id'] !== $productId) {
                        $relatedProducts[] = $relatedProduct;
                    }
                    
                    // Limit to 3 related products
                    if (count($relatedProducts) >= 3) {
                        break;
                    }
                }
            }
            
            error_log("ProductController::detail - Found " . count($relatedProducts) . " related products");
            
            // Prepare page title and meta description
            $pageTitle = htmlspecialchars($product['name']) . ' | Online Grocery Store';
            $metaDescription = 'Buy ' . htmlspecialchars($product['name']) . ' online. Fresh and high-quality products delivered to your door.';
            
            // Store current product in session for cart functionality
            $_SESSION['current_product'] = $product;
            
            error_log("ProductController::detail - About to render product-detail view");
            
            // Output product detail view
            View::output('product-detail', [
                'pageTitle' => $pageTitle,
                'metaDescription' => $metaDescription,
                'product' => $product,
                'categories' => $categories,
                'relatedProducts' => $relatedProducts
            ]);
            
            error_log("ProductController::detail - View rendered successfully");
        } catch (\Exception $e) {
            error_log("ProductController::detail - ERROR: " . $e->getMessage());
            error_log("ProductController::detail - Stack trace: " . $e->getTraceAsString());
            
            // Display error to user
            echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; font-family: sans-serif;">';
            echo '<h1>Something went wrong</h1>';
            echo '<p>We encountered an error while loading the product details. Please try again later.</p>';
            echo '<p><a href="' . View::url('products') . '" style="color: #721c24;">Return to Products</a></p>';
            echo '</div>';
        }
    }
} 