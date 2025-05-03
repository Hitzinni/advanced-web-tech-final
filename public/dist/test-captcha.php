<?php
declare(strict_types=1);

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Include necessary files
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/src/controllers/CaptchaController.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate and display the captcha
$controller = new \App\Controllers\CaptchaController();
$controller->generate(); 