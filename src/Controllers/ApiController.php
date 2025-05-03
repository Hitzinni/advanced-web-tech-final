<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use App\Helpers\Validators;

class ApiController
{
    private $productModel;
    
    public function __construct()
    {
        $this->productModel = new Product();
    }
    
    public function categories(): void
    {
        header('Content-Type: application/json');
        
        $categories = $this->productModel->getCategories();
        
        echo json_encode($categories);
    }
    
    public function products(): void
    {
        header('Content-Type: application/json');
        
        $category = $_GET['cat'] ?? '';
        
        // Validate category if provided
        if (!empty($category) && !Validators::category($category)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid category']);
            exit;
        }
        
        $products = !empty($category)
            ? $this->productModel->getByCategory($category)
            : $this->productModel->getAll();
        
        echo json_encode($products);
    }
} 