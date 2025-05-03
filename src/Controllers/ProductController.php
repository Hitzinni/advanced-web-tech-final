<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;
use App\Models\Product;
use App\Helpers\Validators;

class ProductController
{
    private Product $productModel;
    
    public function __construct()
    {
        $this->productModel = new Product();
    }
    
    public function browse(): void
    {
        $selectedCategory = $_GET['category'] ?? '';
        $selectedProductId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
        
        // Validate category if one is selected
        if (!empty($selectedCategory) && !Validators::category($selectedCategory)) {
            $selectedCategory = '';
        }
        
        $categories = $this->productModel->getCategories();
        $products = $selectedCategory 
            ? $this->productModel->getByCategory($selectedCategory)
            : [];
        
        // Store products in session for cart functionality
        $_SESSION['current_products'] = $products;
        
        $pageTitle = !empty($selectedCategory) 
            ? "Browse {$selectedCategory}" 
            : 'Browse Products';
            
        $metaDescription = !empty($selectedCategory)
            ? "Shop our selection of {$selectedCategory} products. Fresh and high-quality items for delivery."
            : 'Browse our selection of fresh vegetables and quality meats.';
            
        View::output('browse', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'categories' => $categories,
            'products' => $products,
            'selectedCategory' => $selectedCategory,
            'selectedProductId' => $selectedProductId
        ]);
    }
    
    /**
     * Display detailed view of a single product
     */
    public function detail(): void
    {
        // Get product ID from URL
        $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if ($productId <= 0) {
            // Redirect to browse page if no valid product ID
            header('Location: products');
            exit;
        }
        
        // Fetch product details
        $product = $this->productModel->getById($productId);
        
        if (!$product) {
            // If product not found, redirect to browse page
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'Product not found.'
            ];
            header('Location: products');
            exit;
        }
        
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
        
        // Prepare page title and meta description
        $pageTitle = htmlspecialchars($product['name']) . ' | Online Grocery Store';
        $metaDescription = 'Buy ' . htmlspecialchars($product['name']) . ' online. Fresh and high-quality products delivered to your door.';
        
        // Store current product in session for cart functionality
        $_SESSION['current_product'] = $product;
        
        // Output product detail view
        View::output('product-detail', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            'product' => $product,
            'categories' => $categories,
            'relatedProducts' => $relatedProducts
        ]);
    }
} 