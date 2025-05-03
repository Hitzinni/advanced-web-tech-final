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
            
            // Log the paths for debugging
            error_log("Script Name: $scriptName");
            error_log("Request URI: $requestUri");
            
            // Get the URI path from HTTP_HOST and REQUEST_URI
            $httpHost = $_SERVER['HTTP_HOST'] ?? '';
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $fullUrl = "$scheme://$httpHost$requestUri";
            
            // Handle the teaching server environment specifically
            if (strpos($requestUri, '/prin/') !== false) {
                // Extract the base path up to 'public/'
                $publicPos = strpos($requestUri, '/public/');
                if ($publicPos !== false) {
                    // Get everything up to and including '/public/'
                    $basePath = substr($requestUri, 0, $publicPos + 8); // +8 to include '/public/'
                    self::$baseUrl = "$scheme://$httpHost$basePath";
                } else {
                    // If '/public/' is not in the path, use the directory of the script
                    self::$baseUrl = "$scheme://$httpHost" . dirname($requestUri) . '/';
                }
            } else {
                // Local development environment - use relative URLs
                self::$baseUrl = '';
            }
            
            // Ensure base URL ends with a slash if it's not empty
            if (!empty(self::$baseUrl) && substr(self::$baseUrl, -1) !== '/') {
                self::$baseUrl .= '/';
            }
            
            // Log the calculated base URL
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
     * Render a view with data
     *
     * @param string $template Path to template file
     * @param array $data Data to pass to the template
     * @return string Rendered HTML
     */
    public static function render(string $template, array $data = []): string
    {
        try {
            // Add some debug logging
            error_log("View::render - Rendering template: $template");
            
            // Extract variables for use in the template
            extract($data);
            
            // Start output buffering
            ob_start();
            
            // Include the template with error handling
            $template_path = BASE_PATH . '/src/views/' . $template . '.php';
            
            if (!file_exists($template_path)) {
                throw new \Exception("Template file not found: $template_path");
            }
            
            // Include the template
            require $template_path;
            
            // Get the rendered content
            $content = ob_get_clean();
            
            // If we're not rendering the layout, return the content
            if ($template === 'layout') {
                return $content;
            }
            
            // Otherwise, render the layout with this content
            return self::render('layout', array_merge($data, ['content' => $content]));
        } catch (\Exception $e) {
            // Log the error
            error_log('View::render - Error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            
            // Clean any existing output buffer
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Display a simple error message
            ob_start();
            echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; font-family: sans-serif;">';
            echo '<h1>Sorry, something went wrong.</h1>';
            echo '<p>We encountered an error while loading this page. Please try again later.</p>';
            echo '<p><a href="home" style="color: #721c24;">Return to Home</a></p>';
            echo '</div>';
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