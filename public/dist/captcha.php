<?php
declare(strict_types=1);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__DIR__) . '/logs/php_errors.log');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Generate a random 6-character string
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $captchaText = '';
    for ($i = 0; $i < 6; $i++) {
        $captchaText .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    // Store the CAPTCHA text in the session
    $_SESSION['captcha'] = $captchaText;
    
    // Create an image with larger dimensions for better readability and distortion
    $width = 180;
    $height = 60;
    $image = imagecreatetruecolor($width, $height);
    
    // If image creation failed, output an error message and exit
    if ($image === false) {
        throw new Exception("Failed to create image with imagecreatetruecolor()");
    }
    
    // Create a more complex background
    $bgColor = imagecolorallocate($image, 245, 245, 245);
    
    // Create a gradient background
    $gradientColors = [];
    for ($i = 0; $i < $height; $i++) {
        $intensity = (int)(245 - ($i * 20 / $height));
        $gradientColors[$i] = imagecolorallocate($image, $intensity, $intensity, (int)($intensity+10));
    }
    
    // Fill with gradient
    for ($i = 0; $i < $height; $i++) {
        imageline($image, 0, $i, $width, $i, $gradientColors[$i]);
    }
    
    // Add background patterns
    for ($i = 0; $i < 10; $i++) {
        $circleColor = imagecolorallocate($image, 
            random_int(200, 240), 
            random_int(200, 240), 
            random_int(200, 240)
        );
        
        // Draw random ellipses in the background
        imageellipse(
            $image, 
            random_int(0, $width), 
            random_int(0, $height), 
            random_int(20, 70), 
            random_int(20, 70), 
            $circleColor
        );
    }
    
    // Add random noise dots
    for ($i = 0; $i < 250; $i++) {
        $noiseColor = imagecolorallocate($image, 
            random_int(150, 200), 
            random_int(150, 200), 
            random_int(150, 200)
        );
        
        imagesetpixel(
            $image,
            random_int(0, $width),
            random_int(0, $height),
            $noiseColor
        );
    }
    
    // Add confusing lines across the image
    for ($i = 0; $i < 6; $i++) {
        $lineColor = imagecolorallocate($image, 
            random_int(120, 180), 
            random_int(120, 180), 
            random_int(120, 180)
        );
        
        // Create curved lines using bezier curves
        $x1 = random_int(0, $width / 4);
        $y1 = random_int(0, $height);
        $x2 = random_int($width / 4, $width / 2);
        $y2 = random_int(0, $height);
        $x3 = random_int($width / 2, 3 * $width / 4);
        $y3 = random_int(0, $height);
        $x4 = random_int(3 * $width / 4, $width);
        $y4 = random_int(0, $height);
        
        // Draw a bezier curve would be ideal, but PHP GD doesn't have native bezier
        // So we'll use multiple connected lines to simulate a curve
        $points = [$x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4];
        imagepolygon($image, $points, 4, $lineColor);
    }
    
    // Set up fonts
    $fontPath = BASE_PATH . '/public/assets/fonts/';
    $fonts = ['arial.ttf', 'times.ttf', 'verdana.ttf'];
    
    // Add the text with visual distortions
    $x = 15; // Starting position
    for ($i = 0; $i < strlen($captchaText); $i++) {
        $letter = $captchaText[$i];
        
        // Randomize text color for each character
        $textColor = imagecolorallocate($image, 
            random_int(0, 100), 
            random_int(0, 100), 
            random_int(0, 100)
        );
        
        // Randomize font for each character
        $font = $fontPath . $fonts[array_rand($fonts)];
        
        // Randomize size for each character
        $size = random_int(18, 24);
        
        // Randomize angle for each character
        $angle = random_int(-20, 20);
        
        // Randomize y position with slight vertical offset
        $y = random_int($height / 2, $height - 10);
        
        // Validate font exists
        if (!file_exists($font)) {
            // Fall back to built-in font if TTF not found
            imagestring($image, 5, $x, ($height - 20) / 2, $letter, $textColor);
        } else {
            // Use TTF for better distortion
            imagettftext($image, $size, $angle, $x, $y, $textColor, $font, $letter);
        }
        
        // Increment x position, vary spacing slightly for added difficulty
        $x += 20 + random_int(0, 10);
    }
    
    // Add a few foreground confusing elements over the text
    for ($i = 0; $i < 50; $i++) {
        $fgNoiseColor = imagecolorallocate($image, 
            random_int(80, 150), 
            random_int(80, 150), 
            random_int(80, 150)
        );
        
        // Add larger noise dots on top
        imagefilledellipse(
            $image,
            random_int(0, $width),
            random_int(0, $height),
            random_int(1, 3),
            random_int(1, 3),
            $fgNoiseColor
        );
    }
    
    // Log session info
    error_log("CAPTCHA generated: " . $captchaText . " | Session ID: " . session_id());
    
    // Output the image
    header('Content-Type: image/png');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    
    imagepng($image);
    imagedestroy($image);
    exit;
} catch (Throwable $e) {
    // Log the exception
    error_log("CAPTCHA generation error: " . $e->getMessage());
    
    // Output a simple error image
    header('Content-Type: image/png');
    $width = 150;
    $height = 50;
    $image = imagecreatetruecolor($width, $height);
    
    if ($image !== false) {
        $bgColor = imagecolorallocate($image, 255, 200, 200);
        $textColor = imagecolorallocate($image, 255, 0, 0);
        
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
        imagestring($image, 3, 5, 5, "ERROR", $textColor);
        imagestring($image, 2, 5, 25, "See error log", $textColor);
        
        imagepng($image);
        imagedestroy($image);
    } else {
        header('Content-Type: text/plain');
        echo "Critical Error: " . $e->getMessage();
    }
    exit;
} 