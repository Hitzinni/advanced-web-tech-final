<?php
// Simple CAPTCHA generator
session_start();

// Create a random 4-character code
$code = '';
$possible_characters = '23456789ABCDEFGHJKLMNPQRTUVWXY'; // Omitting confusing characters
for ($i = 0; $i < 4; $i++) {
    $code .= $possible_characters[random_int(0, strlen($possible_characters) - 1)];
}

// Store in session
$_SESSION['captcha'] = $code;

// Create image
$width = 150;
$height = 50;
$image = imagecreate($width, $height);

// Define colors
$background = imagecolorallocate($image, 250, 250, 250); // Almost white
$text_color = imagecolorallocate($image, 0, 0, 128);     // Navy blue
$border_color = imagecolorallocate($image, 220, 220, 220); // Light gray
$line_color1 = imagecolorallocate($image, 230, 230, 250); // Very light blue
$line_color2 = imagecolorallocate($image, 240, 240, 210); // Very light yellow

// Add border
imagerectangle($image, 0, 0, $width - 1, $height - 1, $border_color);

// Add decorative lines
// Horizontal wavy line
$y = rand(15, $height - 15);
for ($x = 0; $x < $width; $x += 3) {
    $y_offset = rand(-2, 2);
    imageline($image, $x, $y + $y_offset, $x + 2, $y + $y_offset, $line_color1);
}

// Diagonal line
$x1 = rand(5, 20);
$y1 = rand(5, 15);
$x2 = rand($width - 20, $width - 5);
$y2 = rand($height - 15, $height - 5);
imageline($image, $x1, $y1, $x2, $y2, $line_color2);

// Prepare to draw characters
$total_width = 0;
$font_sizes = [];
$char_widths = [];

// Determine random font sizes and calculate total width
for ($i = 0; $i < strlen($code); $i++) {
    // Use font sizes 3-5 for good readability with variation
    $font_sizes[$i] = rand(3, 5);
    $char_widths[$i] = imagefontwidth($font_sizes[$i]);
    $total_width += $char_widths[$i];
}

// Add spacing between characters (10-15px total)
$spacing = 12;
$total_width += $spacing * (strlen($code) - 1);

// Calculate starting position to center the text
$start_x = ($width - $total_width) / 2;
$current_x = $start_x;

// Draw each character with varied styling
for ($i = 0; $i < strlen($code); $i++) {
    // Get font size and calculate vertical position
    $font_size = $font_sizes[$i];
    $char_height = imagefontheight($font_size);
    
    // Center character vertically with random offset
    $y = ($height - $char_height) / 2 + rand(-3, 3);
    
    // Alternating styling
    if ($i % 2 == 0) {
        // Style 1: With shadow
        $shadow_color = imagecolorallocate($image, 200, 200, 230);
        imagechar($image, $font_size, $current_x + 1, $y + 1, $code[$i], $shadow_color);
        imagechar($image, $font_size, $current_x, $y, $code[$i], $text_color);
    } else {
        // Style 2: Bold effect
        imagechar($image, $font_size, $current_x, $y, $code[$i], $text_color);
        imagechar($image, $font_size, $current_x + 1, $y, $code[$i], $text_color);
    }
    
    // Move to next character position
    $current_x += $char_widths[$i] + $spacing;
}

// Set headers
header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Output image
imagepng($image);
imagedestroy($image);
?> 