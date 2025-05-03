<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Product Model
 * Handles database operations related to products.
 */
class Product extends BaseModel
{
    /**
     * Retrieves a unique list of all product categories.
     * It fetches distinct categories present in the `product` table
     * and merges them with a predefined list of core categories to ensure all are represented.
     * 
     * @return array A sorted array of unique category names.
     */
    public function getCategories(): array
    {
        // SQL query to get distinct categories currently associated with products
        $sql = "SELECT DISTINCT category FROM product ORDER BY category";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        // Fetch results and extract the 'category' column into a simple array
        $results = $stmt->fetchAll();
        $categories = array_column($results, 'category');
        
        // Define the core list of expected categories (acts like an enum)
        $allCategories = ['Vegetables', 'Fruits', 'Meat', 'Bakery', 'Dairy'];
        
        // Merge the categories found in the DB with the core list, 
        // remove duplicates, and re-index the array.
        return array_values(array_unique(array_merge($categories, $allCategories)));
    }
    
    /**
     * Retrieves all products belonging to a specific category.
     * 
     * @param string $category The name of the category to filter by.
     * @return array An array of associative arrays, each representing a product in the specified category.
     */
    public function getByCategory(string $category): array
    {
        // SQL query to select products by category, ordered by name
        $sql = "SELECT * FROM product WHERE category = ? ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category]);
        
        // Fetch and return all matching products
        return $stmt->fetchAll();
    }
    
    /**
     * Retrieves a single product by its unique ID.
     * 
     * @param int $id The ID of the product to retrieve.
     * @return array|null An associative array of the product data if found, otherwise null.
     */
    public function getById(int $id): ?array
    {
        // SQL query to select a product by its primary key ID
        $sql = "SELECT * FROM product WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        // Fetch the product data
        $product = $stmt->fetch();
        // Return the product array or null if not found
        return $product ?: null;
    }
    
    /**
     * Retrieves all products from the database, ordered by category and then by name.
     * 
     * @return array An array of associative arrays, each representing a product.
     */
    public function getAll(): array
    {
        // SQL query to select all products, ordered for consistent display
        $sql = "SELECT * FROM product ORDER BY category, name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        // Fetch and return all products
        return $stmt->fetchAll();
    }
} 