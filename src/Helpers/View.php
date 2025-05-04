<?php
declare(strict_types=1);

namespace App\Helpers;

class View
{
    /**
     * Base URL for the application
     * This will be calculated the first time it's needed
     */
    private static $baseUrl = null;
    
    /**
     * Get the base URL for the application
     * 
     * @return string The base URL
     */
    public static function getBaseUrl(): string
    {
        if (self::$baseUrl === null) {
            // Get the script path and name
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            $httpHost = $_SERVER['HTTP_HOST'] ?? '';
            
            // Log the paths for debugging
            error_log("Script Name: $scriptName");
            error_log("Request URI: $requestUri");
            
            // Always use HTTPS
            $scheme = 'https';
            
            // Handle SCM teaching server specifically - improved handling for spaces in paths
            if (strpos($httpHost, 'teach.scam.keele.ac.uk') !== false) {
                // Try to find 'public/' in the path using a more robust approach
                // This works even with encoded spaces (%20) in the path
                if (preg_match('|(.*?/public)/|', $scriptName, $matches)) {
                    // Use script name since it's more reliable on this server
                    $basePath = $matches[1];
                    self::$baseUrl = "$scheme://$httpHost$basePath/";
                    error_log("Found public/ in script name: $basePath");
                } else if (preg_match('|(.*?/public)/|', $requestUri, $matches)) {
                    // Fallback to requestUri
                    $basePath = $matches[1];
                    self::$baseUrl = "$scheme://$httpHost$basePath/";
                    error_log("Found public/ in request URI: $basePath");
                } else {
                    // Last resort fallback
                    $basePath = dirname($scriptName);
                    self::$baseUrl = "$scheme://$httpHost$basePath/";
                    error_log("Fallback path from script name: $basePath");
                }
                
                // Ensure we're using index.php for routing all requests
                if (strpos(self::$baseUrl, 'index.php') === false) {
                    error_log("Base URL is: " . self::$baseUrl . " - ensuring we use index.php for routing");
                }
            } else {
                // For other environments
                $basePath = dirname($scriptName);
                self::$baseUrl = "$scheme://$httpHost$basePath/";
                
                // If we're in the public directory, use that as the base path
                if (basename($basePath) === 'public') {
                    self::$baseUrl = "$scheme://$httpHost$basePath/";
                }
            }
            
            // Ensure base URL ends with a slash
            if (substr(self::$baseUrl, -1) !== '/') {
                self::$baseUrl .= '/';
            }
            
            // Force HTTPS in the URL
            self::$baseUrl = str_replace('http://', 'https://', self::$baseUrl);
            
            error_log("View::getBaseUrl - Calculated base URL: " . self::$baseUrl);
        }
        
        return self::$baseUrl;
    }
    
    /**
     * Generate a URL for an asset
     * 
     * @param string $path Relative path to the asset
     * @return string Full URL to the asset
     */
    public static function asset(string $path): string
    {
        // Remove leading slash if present
        $path = ltrim($path, '/');
        $url = self::getBaseUrl() . $path;
        
        // Log the generated asset URL
        error_log("Asset URL generated: $url for path: $path");
        
        return $url;
    }
    
    /**
     * Generate a URL for a route
     * 
     * @param string $route The route name (e.g., 'products', 'login')
     * @param array $params Optional query parameters
     * @return string Full URL for the route
     */
    public static function url(string $route, array $params = []): string
    {
        $baseUrl = self::getBaseUrl();
        
        // Ensure route doesn't start with a slash
        $route = ltrim($route, '/');
        
        // For API routes, use direct paths
        $isApiRoute = strpos($route, 'api/') === 0;
        
        // Create the full URL - use index.php?route= for non-API routes
        if ($isApiRoute) {
            $url = $baseUrl . $route;
            error_log("API URL generated: $url for route: $route");
        } else {
            // Add index.php?route= for non-API routes
            $url = $baseUrl . 'index.php?route=' . $route;
            error_log("Route URL generated: $url for route: $route");
        }
        
        // Add query parameters if provided
        if (!empty($params)) {
            $separator = strpos($url, '?') !== false ? '&' : '?';
            $url .= $separator . http_build_query($params);
        }
        
        // Force HTTPS in the URL
        $url = str_replace('http://', 'https://', $url);
        
        error_log("Final URL generated: $url for route: $route");
        
        return $url;
    }
    
    /**
     * Render a view with data
     *
     * @param string $template Path to template file
     * @param array $data Data to pass to the template
     * @return string Rendered HTML
     */
    public static function render(string $template, array $data = []): string
    {
        try {
            // Add detailed debug logging
            error_log("View::render - Starting to render template: $template at " . date('Y-m-d H:i:s'));
            
            // Construct the template path
            $template_path = BASE_PATH . '/src/views/' . $template . '.php';
            error_log("View::render - Full template path: $template_path");
            
            // Check if file exists before trying to include it
            if (!file_exists($template_path)) {
                error_log("View::render - ERROR: Template file not found: $template_path");
                error_log("View::render - Current working directory: " . getcwd());
                error_log("View::render - BASE_PATH value: " . BASE_PATH);
                
                // Try directory listing to see what's available
                $viewsDir = BASE_PATH . '/src/views';
                if (is_dir($viewsDir)) {
                    $files = scandir($viewsDir);
                    error_log("View::render - Files in views directory: " . implode(", ", $files));
                } else {
                    error_log("View::render - Views directory does not exist: $viewsDir");
                }
                
                throw new \Exception("Template file not found: $template_path");
            }
            
            error_log("View::render - Template file found, reading contents...");
            
            // Extract variables for use in the template
            extract($data);
            
            // Start output buffering
            ob_start();
            
            // Include the template
            require $template_path;
            
            // Get the rendered content
            $content = ob_get_clean();
            
            // Check if content was captured
            if (empty($content)) {
                error_log("View::render - WARNING: Empty content after rendering template: $template");
            } else {
                error_log("View::render - Successfully rendered template: $template (content length: " . strlen($content) . " bytes)");
            }
            
            // If we're not rendering the layout, return the content
            if ($template === 'layout') {
                return $content;
            }
            
            // Otherwise, render the layout with this content
            error_log("View::render - Now rendering layout with content from: $template");
            return self::render('layout', array_merge($data, ['content' => $content]));
        } catch (\Throwable $e) {
            // Change Exception to Throwable to catch all error types
            // Log the error with detailed information
            error_log('View::render - CRITICAL ERROR: ' . $e->getMessage());
            error_log('View::render - Exception trace: ' . $e->getTraceAsString());
            
            // Clean any existing output buffer
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Determine whether we should show detailed errors
            $isDebug = isset($_GET['debug']) && $_GET['debug'] === 'true';
            
            // Generate a fallback response based on the template we were trying to render
            ob_start();
            
            // If it's the cart template specifically, render a functional basic cart
            if ($template === 'cart') {
                // Extract cart data if available
                $cartItems = $data['cartItems'] ?? [];
                $cartTotal = $data['cartTotal'] ?? 0;
                $baseUrl = self::getBaseUrl();
                
                echo '<div style="margin: 20px auto; max-width: 1000px; padding: 20px;">';
                echo '<div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 5px;">';
                echo '<strong>Notice:</strong> Using simplified cart display due to template rendering issues.';
                echo '</div>';
                
                echo '<h1>Shopping Cart</h1>';
                
                if (empty($cartItems)) {
                    echo '<div style="padding: 30px; text-align: center; background-color: #f8f9fa; border-radius: 5px; margin: 20px 0;">';
                    echo '<h3>Your cart is empty</h3>';
                    echo '<p>Browse our products to add items to your cart.</p>';
                    echo '<a href="' . $baseUrl . 'index.php?route=products" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;">Browse Products</a>';
                    echo '</div>';
                } else {
                    echo '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
                    echo '<thead style="background-color: #f8f9fa;">';
                    echo '<tr>';
                    echo '<th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Product</th>';
                    echo '<th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Price</th>';
                    echo '<th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Quantity</th>';
                    echo '<th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Total</th>';
                    echo '<th style="padding: 12px; text-align: center; border-bottom: 2px solid #dee2e6;">Actions</th>';
                    echo '</tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    foreach ($cartItems as $item) {
                        $itemId = isset($item['id']) ? (int)$item['id'] : 0;
                        $itemName = isset($item['name']) ? htmlspecialchars((string)$item['name']) : 'Unknown Product';
                        $itemPrice = isset($item['price']) && is_numeric($item['price']) ? (float)$item['price'] : 0;
                        $itemQuantity = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;
                        $itemTotal = $itemPrice * $itemQuantity;
                        
                        echo '<tr style="border-bottom: 1px solid #dee2e6;">';
                        echo '<td style="padding: 12px;">' . $itemName . '</td>';
                        echo '<td style="padding: 12px; text-align: center;">$' . number_format($itemPrice, 2) . '</td>';
                        echo '<td style="padding: 12px; text-align: center;">';
                        echo '<form method="POST" action="' . $baseUrl . 'index.php?route=api/cart/update" style="display: flex; justify-content: center; align-items: center;">';
                        echo '<input type="hidden" name="product_id" value="' . $itemId . '">';
                        echo '<input type="number" name="quantity" value="' . $itemQuantity . '" min="1" style="width: 60px; text-align: center; padding: 5px;">';
                        echo '<button type="submit" style="margin-left: 5px; padding: 5px 10px; background-color: #6c757d; border: none; color: white; border-radius: 3px;">Update</button>';
                        echo '</form>';
                        echo '</td>';
                        echo '<td style="padding: 12px; text-align: center;">$' . number_format($itemTotal, 2) . '</td>';
                        echo '<td style="padding: 12px; text-align: center;">';
                        echo '<a href="' . $baseUrl . 'index.php?route=api/cart/remove&product_id=' . $itemId . '" style="padding: 5px 10px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 3px;">Remove</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '<tfoot>';
                    echo '<tr style="background-color: #f8f9fa;">';
                    echo '<td colspan="3" style="padding: 12px; text-align: right;"><strong>Total:</strong></td>';
                    echo '<td style="padding: 12px; text-align: center;"><strong>$' . number_format($cartTotal, 2) . '</strong></td>';
                    echo '<td></td>';
                    echo '</tr>';
                    echo '</tfoot>';
                    echo '</table>';
                    
                    echo '<div style="display: flex; justify-content: space-between; margin-top: 20px;">';
                    echo '<a href="' . $baseUrl . 'index.php?route=products" style="padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px;">Continue Shopping</a>';
                    echo '<a href="' . $baseUrl . 'index.php?route=checkout" style="padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px;">Proceed to Checkout</a>';
                    echo '</div>';
                }
                
                echo '</div>';
            } else {
                // Generic error display for other templates
                echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; font-family: sans-serif;">';
                echo '<h1>Template Error</h1>';
                
                if ($isDebug) {
                    echo '<p><strong>Error message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '<p><strong>Template:</strong> ' . htmlspecialchars($template) . '</p>';
                    echo '<p><strong>Template path:</strong> ' . htmlspecialchars($template_path ?? 'Unknown') . '</p>';
                    echo '<p><strong>BASE_PATH:</strong> ' . htmlspecialchars(BASE_PATH) . '</p>';
                    echo '<p><strong>Stack trace:</strong></p>';
                    echo '<pre style="background: #f1f1f1; padding: 10px; overflow: auto; max-height: 300px;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
                } else {
                    echo '<p>There was a problem displaying this page.</p>';
                }
                
                echo '<p><a href="' . self::url('home') . '" style="color: #721c24;">Return to Home</a></p>';
                echo '</div>';
            }
            
            return ob_get_clean();
        }
    }
    
    /**
     * Output a rendered view
     *
     * @param string $template Path to template file
     * @param array $data Data to pass to the template
     */
    public static function output(string $template, array $data = []): void
    {
        echo self::render($template, $data);
    }
} 