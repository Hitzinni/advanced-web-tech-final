<?php
// This is a text-only captcha that doesn't use any GD library
session_start();

// Generate a 6-character mixed alphanumeric code
$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captchaCode = '';
for ($i = 0; $i < 6; $i++) {
    $captchaCode .= $characters[rand(0, strlen($characters) - 1)];
}
$_SESSION['captcha'] = $captchaCode;

// Log for debugging
error_log("Text captcha generated: " . $captchaCode);

// Output as plain text
header('Content-Type: text/plain');
header('Cache-Control: no-store, no-cache, must-revalidate');
echo $captchaCode;
exit; 