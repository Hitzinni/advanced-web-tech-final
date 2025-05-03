<?php
declare(strict_types=1);

namespace App\Controllers;

class CaptchaController
{
    private $fonts = [
        'arial.ttf',
        'verdana.ttf',
        'times.ttf'
    ];
    
    public function generate(): void
    {
        try {
            // Start the session to store the CAPTCHA text
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Generate a random 6-character string
            $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
            $captchaText = '';
            for ($i = 0; $i < 6; $i++) {
                $captchaText .= $characters[random_int(0, strlen($characters) - 1)];
            }
            
            // Store the CAPTCHA text in the session
            $_SESSION['captcha'] = $captchaText;
            
            // Create an image
            $width = 150;
            $height = 50;
            $image = imagecreatetruecolor($width, $height);
            
            // If image creation failed, output an error message and exit
            if ($image === false) {
                $this->outputError("Failed to create image with imagecreatetruecolor()");
                return;
            }
            
            // Colors
            $bgColor = imagecolorallocate($image, 248, 248, 248);
            $textColor = imagecolorallocate($image, 0, 0, 0);
            $noiseColor = imagecolorallocate($image, 100, 120, 180);
            
            if ($bgColor === false || $textColor === false || $noiseColor === false) {
                $this->outputError("Failed to allocate colors");
                imagedestroy($image);
                return;
            }
            
            // Fill background
            imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
            
            // Add some noise
            for ($i = 0; $i < 200; $i++) {
                imagesetpixel(
                    $image,
                    random_int(0, $width),
                    random_int(0, $height),
                    $noiseColor
                );
            }
            
            // Add lines
            for ($i = 0; $i < 4; $i++) {
                imageline(
                    $image,
                    random_int(0, $width),
                    random_int(0, $height),
                    random_int(0, $width),
                    random_int(0, $height),
                    $noiseColor
                );
            }
            
            // Add the text
            $fontsPath = dirname(__DIR__, 2) . '/public/assets/fonts/';
            $font = $fontsPath . $this->fonts[array_rand($this->fonts)];
            $x = 10;
            
            // Debug font path
            $fallbackUsed = false;
            if (!file_exists($font)) {
                error_log("CAPTCHA font not found: $font");
                $fallbackUsed = true;
                
                // Try each font until we find one that exists
                foreach ($this->fonts as $fontFile) {
                    $testPath = $fontsPath . $fontFile;
                    if (file_exists($testPath)) {
                        $font = $testPath;
                        $fallbackUsed = false;
                        error_log("Using alternative font: $font");
                        break;
                    }
                }
            }
            
            // If no fonts are found, use the built-in fonts
            if ($fallbackUsed) {
                error_log("All CAPTCHA fonts missing, using built-in font");
                for ($i = 0; $i < strlen($captchaText); $i++) {
                    $letter = $captchaText[$i];
                    imagestring($image, 5, $x, ($height - 20) / 2, $letter, $textColor);
                    $x += 20;
                }
            } else {
                // Use TTF font
                for ($i = 0; $i < strlen($captchaText); $i++) {
                    $letter = $captchaText[$i];
                    $angle = random_int(-15, 15);
                    $size = random_int(14, 24);
                    $y = random_int(30, 40);
                    
                    $result = imagettftext($image, $size, $angle, $x, $y, $textColor, $font, $letter);
                    if ($result === false) {
                        error_log("Failed to render text using TTF font: $font");
                        // Fallback to basic font if TTF fails
                        imagestring($image, 5, $x, ($height - 20) / 2, $letter, $textColor);
                    }
                    $x += 20;
                }
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
        } catch (\Throwable $e) {
            // Log the exception
            error_log("CAPTCHA generation error: " . $e->getMessage());
            $this->outputError("Error generating CAPTCHA: " . $e->getMessage());
        }
    }
    
    private function outputError(string $message): void
    {
        header('Content-Type: image/png');
        // Create a simple error image
        $width = 150;
        $height = 50;
        $image = imagecreatetruecolor($width, $height);
        
        if ($image === false) {
            header('Content-Type: text/plain');
            echo "Critical Error: " . $message;
            exit;
        }
        
        $bgColor = imagecolorallocate($image, 255, 200, 200);
        $textColor = imagecolorallocate($image, 255, 0, 0);
        
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
        imagestring($image, 3, 5, 5, "ERROR", $textColor);
        imagestring($image, 2, 5, 25, "See error log", $textColor);
        
        imagepng($image);
        imagedestroy($image);
        exit;
    }
} 