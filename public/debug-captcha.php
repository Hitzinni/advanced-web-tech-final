<?php
declare(strict_types=1);

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include necessary files
require_once BASE_PATH . '/vendor/autoload.php';

// Set content type
header('Content-Type: text/plain');

// Output system information
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server OS: " . PHP_OS . "\n";
echo "Web Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n\n";

// Check if GD is enabled
echo "GD Extension: " . (extension_loaded('gd') ? 'Enabled' : 'Disabled') . "\n";

if (function_exists('gd_info')) {
    $gdInfo = gd_info();
    echo "GD Version: " . $gdInfo['GD Version'] . "\n";
    echo "FreeType Support: " . ($gdInfo['FreeType Support'] ? 'Yes' : 'No') . "\n";
    echo "TTF Support: " . (isset($gdInfo['TTF Support']) && $gdInfo['TTF Support'] ? 'Yes' : 'No') . "\n\n";
} else {
    echo "Could not get GD info\n\n";
}

// Check font files
$fontFiles = ['arial.ttf', 'verdana.ttf', 'times.ttf'];
$fontsDir = BASE_PATH . '/public/assets/fonts';

echo "Font Directory: $fontsDir\n";
echo "Font Directory exists: " . (is_dir($fontsDir) ? 'Yes' : 'No') . "\n";

if (is_dir($fontsDir)) {
    echo "Font Directory permissions: " . substr(sprintf('%o', fileperms($fontsDir)), -4) . "\n\n";
    
    echo "Font Files:\n";
    foreach ($fontFiles as $fontFile) {
        $fullPath = $fontsDir . '/' . $fontFile;
        
        echo "- $fontFile: " . (file_exists($fullPath) ? 'Exists' : 'Missing') . "\n";
        
        if (file_exists($fullPath)) {
            echo "  Size: " . filesize($fullPath) . " bytes\n";
            echo "  Permissions: " . substr(sprintf('%o', fileperms($fullPath)), -4) . "\n";
            echo "  Readable: " . (is_readable($fullPath) ? 'Yes' : 'No') . "\n";
        }
    }
} else {
    echo "Font directory does not exist or is not accessible\n";
}

// Try to create a sample image with text
echo "\nTesting image generation:\n";

try {
    // Create image
    $image = imagecreatetruecolor(150, 50);
    
    if ($image === false) {
        echo "Failed to create image with imagecreatetruecolor()\n";
    } else {
        echo "Successfully created base image\n";
        
        // Create colors
        $bgColor = imagecolorallocate($image, 248, 248, 248);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        
        if ($bgColor === false || $textColor === false) {
            echo "Failed to allocate colors\n";
        } else {
            echo "Successfully allocated colors\n";
            
            // Fill background
            imagefilledrectangle($image, 0, 0, 150, 50, $bgColor);
            
            // Try to use TTF
            $fontPath = $fontsDir . '/arial.ttf';
            
            if (file_exists($fontPath) && is_readable($fontPath)) {
                echo "Using TTF font: $fontPath\n";
                
                try {
                    $result = imagettftext($image, 16, 0, 10, 30, $textColor, $fontPath, 'Test');
                    
                    if ($result === false) {
                        echo "Failed to render TTF text\n";
                    } else {
                        echo "Successfully rendered TTF text\n";
                    }
                } catch (Exception $e) {
                    echo "Exception rendering TTF text: " . $e->getMessage() . "\n";
                }
            } else {
                echo "TTF font not available, using default font\n";
                imagestring($image, 5, 10, 15, 'Test', $textColor);
            }
        }
        
        // Clean up
        imagedestroy($image);
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\nEnd of diagnostic information"; 