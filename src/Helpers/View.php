<?php
declare(strict_types=1);

namespace App\Helpers;

class View
{
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