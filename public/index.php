<?php
declare(strict_types=1);

// DEBUG MODE - Remove this in production
// This will help identify why we're getting a 500 error
// Diagnostic information
if (isset($_GET['debug']) && $_GET['debug'] === 'info') {
    header('Content-Type: text/plain');
    echo "PHP Version: " . phpversion() . "\n";
    echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
    echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
    echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
    echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
    echo "Current Directory: " . getcwd() . "\n";
    exit;
}

session_start();

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Autoload classes
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

// For debugging - uncomment to see the current route
// echo "<pre>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</pre>";

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1'); // Temporarily show errors to diagnose the issue
ini_set('log_errors', '1');     // Log errors instead

// Make sure logs directory exists
$logsDir = BASE_PATH . '/logs';
if (!is_dir($logsDir)) {
    // Try to create the directory if it doesn't exist
    if (!mkdir($logsDir, 0755, true)) {
        // If we can't create it, use the system's temp directory
        $logsDir = sys_get_temp_dir();
    }
}
ini_set('error_log', $logsDir . '/php_errors.log'); // Set a custom error log

// Set up custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    
    // Don't display errors directly to users in production
    return true;
});

// Set up exception handler
set_exception_handler(function($exception) {
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    error_log($exception->getTraceAsString());
    
    // Display a friendly error page
    http_response_code(500);
    echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; font-family: sans-serif;">';
    echo '<h1>Sorry, something went wrong.</h1>';
    echo '<p>We encountered an error while processing your request. Please try again later.</p>';
    echo '<p><a href="home" style="color: #721c24;">Return to Home</a></p>';
    echo '</div>';
    exit;
});

// Get route from URL path or query string for backward compatibility
$route = $_GET['route'] ?? '';

// If no route provided in the query string, try to get it from the URL path
if (empty($route)) {
    // Get the request URI
    $requestUri = $_SERVER['REQUEST_URI'];
    
    // Remove query string if present
    $uriPath = parse_url($requestUri, PHP_URL_PATH);
    
    // Remove leading slash and any reference to the application path
    $cleanPath = ltrim($uriPath, '/');
    
    // More aggressive cleaning for teaching server environments
    // This will remove any path segments before and including 'public'
    if (strpos($cleanPath, 'public') !== false) {
        $parts = explode('public', $cleanPath, 2);
        $cleanPath = isset($parts[1]) ? ltrim($parts[1], '/') : '';
    }
    
    // Also handle direct access to index.php
    $cleanPath = preg_replace('#^index\.php/?#', '', $cleanPath);
    
    // If the path is empty, default to 'home'
    $route = $cleanPath ?: 'home';
}

// Display for debugging - uncomment if needed
// echo "<pre>ROUTE: " . $route . "</pre>";

// Initialize CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Routes
switch ($route) {
    case '':
    case 'home':
        require_once BASE_PATH . '/src/controllers/HomeController.php';
        $controller = new \App\Controllers\HomeController();
        $controller->index();
        break;
        
    case 'about':
    case 'about-us':
        require_once BASE_PATH . '/src/controllers/AboutController.php';
        $controller = new \App\Controllers\AboutController();
        $controller->index();
        break;
        
    case 'browse':
    case 'products':
        require_once BASE_PATH . '/src/controllers/ProductController.php';
        $controller = new \App\Controllers\ProductController();
        $controller->browse();
        break;
        
    case 'product':
        require_once BASE_PATH . '/src/controllers/ProductController.php';
        $controller = new \App\Controllers\ProductController();
        $controller->detail();
        break;
        
    case 'login':
        require_once BASE_PATH . '/src/controllers/AuthController.php';
        $controller = new \App\Controllers\AuthController();
        $controller->loginForm();
        break;
        
    case 'login-post':
        require_once BASE_PATH . '/src/controllers/AuthController.php';
        $controller = new \App\Controllers\AuthController();
        $controller->login();
        break;
        
    case 'register':
        require_once BASE_PATH . '/src/controllers/AuthController.php';
        $controller = new \App\Controllers\AuthController();
        $controller->registerForm();
        break;
        
    case 'register-post':
        require_once BASE_PATH . '/src/controllers/AuthController.php';
        $controller = new \App\Controllers\AuthController();
        $controller->register();
        break;
        
    case 'logout':
        require_once BASE_PATH . '/src/controllers/AuthController.php';
        $controller = new \App\Controllers\AuthController();
        $controller->logout();
        break;
        
    case 'change-password':
        require_once BASE_PATH . '/src/controllers/AuthController.php';
        $controller = new \App\Controllers\AuthController();
        $controller->changePasswordForm();
        break;
        
    case 'process-password-change':
        require_once BASE_PATH . '/src/controllers/AuthController.php';
        $controller = new \App\Controllers\AuthController();
        $controller->changePassword();
        break;
        
    case 'order':
        require_once BASE_PATH . '/src/controllers/OrderController.php';
        $controller = new \App\Controllers\OrderController();
        $controller->create();
        break;
        
    case 'order-receipt':
        require_once BASE_PATH . '/src/controllers/OrderController.php';
        $controller = new \App\Controllers\OrderController();
        $controller->show();
        break;
        
    case 'my-orders':
        require_once BASE_PATH . '/src/controllers/OrderController.php';
        $controller = new \App\Controllers\OrderController();
        $controller->myOrders();
        break;
        
    case 'update-order-status':
        require_once BASE_PATH . '/src/controllers/OrderController.php';
        $controller = new \App\Controllers\OrderController();
        $controller->updateStatus();
        break;
        
    // Cart routes
    case 'cart':
        require_once BASE_PATH . '/src/controllers/CartController.php';
        $controller = new \App\Controllers\CartController();
        $controller->viewCart();
        break;
        
    case 'api/cart/add':
        require_once BASE_PATH . '/src/controllers/CartController.php';
        $controller = new \App\Controllers\CartController();
        $controller->addToCart();
        break;
        
    case 'api/cart/update':
        require_once BASE_PATH . '/src/controllers/CartController.php';
        $controller = new \App\Controllers\CartController();
        $controller->updateCartItem();
        break;
        
    case 'api/cart/remove':
        require_once BASE_PATH . '/src/controllers/CartController.php';
        $controller = new \App\Controllers\CartController();
        $controller->removeCartItem();
        break;
        
    case 'api/cart/clear':
        require_once BASE_PATH . '/src/controllers/CartController.php';
        $controller = new \App\Controllers\CartController();
        $controller->clearCart();
        break;
        
    case 'api/cart/count':
        require_once BASE_PATH . '/src/controllers/CartController.php';
        $controller = new \App\Controllers\CartController();
        $controller->getCartCount();
        break;
        
    case 'checkout':
        require_once BASE_PATH . '/src/controllers/OrderController.php';
        $controller = new \App\Controllers\OrderController();
        $controller->checkout();
        break;
        
    case 'process-checkout':
        require_once BASE_PATH . '/src/controllers/OrderController.php';
        $controller = new \App\Controllers\OrderController();
        $controller->processCheckout();
        break;
        
    case 'api/categories':
        require_once BASE_PATH . '/src/controllers/ApiController.php';
        $controller = new \App\Controllers\ApiController();
        $controller->categories();
        break;
        
    case 'api/products':
        require_once BASE_PATH . '/src/controllers/ApiController.php';
        $controller = new \App\Controllers\ApiController();
        $controller->products();
        break;
        
    case 'api/captcha':
        require_once BASE_PATH . '/src/controllers/CaptchaController.php';
        $controller = new \App\Controllers\CaptchaController();
        $controller->generate();
        break;
        
    case 'api/orders':
        require_once BASE_PATH . '/src/controllers/ManagerController.php';
        $controller = new \App\Controllers\ManagerController();
        $controller->show();
        break;
        
    // Manager routes
    case 'manager-users':
        require_once BASE_PATH . '/src/controllers/ManagerController.php';
        $controller = new \App\Controllers\ManagerController();
        $controller->users();
        break;
        
    case 'manager-orders':
        require_once BASE_PATH . '/src/controllers/ManagerController.php';
        $controller = new \App\Controllers\ManagerController();
        $controller->orders();
        break;
        
    case 'update-user-role':
        require_once BASE_PATH . '/src/controllers/ManagerController.php';
        $controller = new \App\Controllers\ManagerController();
        $controller->updateUserRole();
        break;
        
    case 'delete-user':
        require_once BASE_PATH . '/src/controllers/ManagerController.php';
        $controller = new \App\Controllers\ManagerController();
        $controller->deleteUser();
        break;
        
    case 'reset-user-password':
        require_once BASE_PATH . '/src/controllers/ManagerController.php';
        $controller = new \App\Controllers\ManagerController();
        $controller->resetUserPassword();
        break;
        
    // Review routes
    case 'review':
        require_once BASE_PATH . '/src/controllers/ReviewController.php';
        $controller = new \App\Controllers\ReviewController();
        $controller->showReviewForm();
        break;
        
    case 'review-submit':
        require_once BASE_PATH . '/src/controllers/ReviewController.php';
        $controller = new \App\Controllers\ReviewController();
        $controller->submitReview();
        break;
        
    case 'review-delete':
        require_once BASE_PATH . '/src/controllers/ReviewController.php';
        $controller = new \App\Controllers\ReviewController();
        $controller->deleteReview();
        break;
        
    // Newsletter route
    case 'newsletter-subscribe':
        require_once BASE_PATH . '/src/controllers/NewsletterController.php';
        $controller = new \App\Controllers\NewsletterController();
        $controller->subscribe();
        break;
        
    default:
        // Check if the route starts with 'api/'
        if (strpos($route, 'api/') === 0) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'API endpoint not found',
                'requested_route' => $route
            ]);
            exit;
        }
        
        // Show 404 page with the View helper
        http_response_code(404);
        require_once BASE_PATH . '/src/Helpers/View.php';
        require_once BASE_PATH . '/src/controllers/ErrorController.php';
        
        $controller = new \App\Controllers\ErrorController();
        $controller->notFound();
        break;
} 