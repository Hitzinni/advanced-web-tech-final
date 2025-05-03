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
ini_set('display_errors', '0'); // Don't display errors directly to users
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

// Add a safe require function to better handle missing files
function safeRequire($filePath) {
    if (file_exists($filePath)) {
        require_once $filePath;
        return true;
    } else {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; font-family: sans-serif;">';
        echo '<h1>Controller Not Found</h1>';
        echo "<p>The file <code>$filePath</code> does not exist.</p>";
        echo '<p>This could be due to:</p>';
        echo '<ul>';
        echo '<li>Case sensitivity issues in the file path</li>';
        echo '<li>Missing controller files in your project</li>';
        echo '<li>Directory permission issues</li>';
        echo '</ul>';
        echo '</div>';
        return false;
    }
}

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
        if (safeRequire(BASE_PATH . '/src/Controllers/HomeController.php')) {
            $controller = new \App\Controllers\HomeController();
            $controller->index();
        }
        break;
        
    case 'about':
    case 'about-us':
        if (safeRequire(BASE_PATH . '/src/Controllers/AboutController.php')) {
            $controller = new \App\Controllers\AboutController();
            $controller->index();
        }
        break;
        
    case 'browse':
    case 'products':
        if (safeRequire(BASE_PATH . '/src/Controllers/ProductController.php')) {
            $controller = new \App\Controllers\ProductController();
            $controller->browse();
        }
        break;
        
    case 'product':
        if (safeRequire(BASE_PATH . '/src/Controllers/ProductController.php')) {
            $controller = new \App\Controllers\ProductController();
            $controller->detail();
        }
        break;
        
    case 'login':
        if (safeRequire(BASE_PATH . '/src/Controllers/AuthController.php')) {
            $controller = new \App\Controllers\AuthController();
            $controller->loginForm();
        }
        break;
        
    case 'login-post':
        if (safeRequire(BASE_PATH . '/src/Controllers/AuthController.php')) {
            $controller = new \App\Controllers\AuthController();
            $controller->login();
        }
        break;
        
    case 'register':
        if (safeRequire(BASE_PATH . '/src/Controllers/AuthController.php')) {
            $controller = new \App\Controllers\AuthController();
            $controller->registerForm();
        }
        break;
        
    case 'register-post':
        if (safeRequire(BASE_PATH . '/src/Controllers/AuthController.php')) {
            $controller = new \App\Controllers\AuthController();
            $controller->register();
        }
        break;
        
    case 'logout':
        if (safeRequire(BASE_PATH . '/src/Controllers/AuthController.php')) {
            $controller = new \App\Controllers\AuthController();
            $controller->logout();
        }
        break;
        
    case 'change-password':
        if (safeRequire(BASE_PATH . '/src/Controllers/AuthController.php')) {
            $controller = new \App\Controllers\AuthController();
            $controller->changePasswordForm();
        }
        break;
        
    case 'process-password-change':
        if (safeRequire(BASE_PATH . '/src/Controllers/AuthController.php')) {
            $controller = new \App\Controllers\AuthController();
            $controller->changePassword();
        }
        break;
        
    case 'order':
        safeRequire(BASE_PATH . '/src/Controllers/OrderController.php');
        $controller = new \App\Controllers\OrderController();
        $controller->create();
        break;
        
    case 'order-receipt':
        safeRequire(BASE_PATH . '/src/Controllers/OrderController.php');
        $controller = new \App\Controllers\OrderController();
        $controller->show();
        break;
        
    case 'my-orders':
        safeRequire(BASE_PATH . '/src/Controllers/OrderController.php');
        $controller = new \App\Controllers\OrderController();
        $controller->myOrders();
        break;
        
    case 'update-order-status':
        safeRequire(BASE_PATH . '/src/Controllers/OrderController.php');
        $controller = new \App\Controllers\OrderController();
        $controller->updateStatus();
        break;
        
    // Cart routes
    case 'cart':
        safeRequire(BASE_PATH . '/src/Controllers/CartController.php');
        $controller = new \App\Controllers\CartController();
        $controller->viewCart();
        break;
        
    case 'api/cart/add':
        safeRequire(BASE_PATH . '/src/Controllers/CartController.php');
        $controller = new \App\Controllers\CartController();
        $controller->addToCart();
        break;
        
    case 'api/cart/update':
        safeRequire(BASE_PATH . '/src/Controllers/CartController.php');
        $controller = new \App\Controllers\CartController();
        $controller->updateCartItem();
        break;
        
    case 'api/cart/remove':
        safeRequire(BASE_PATH . '/src/Controllers/CartController.php');
        $controller = new \App\Controllers\CartController();
        $controller->removeCartItem();
        break;
        
    case 'api/cart/clear':
        safeRequire(BASE_PATH . '/src/Controllers/CartController.php');
        $controller = new \App\Controllers\CartController();
        $controller->clearCart();
        break;
        
    case 'api/cart/count':
        safeRequire(BASE_PATH . '/src/Controllers/CartController.php');
        $controller = new \App\Controllers\CartController();
        $controller->getCartCount();
        break;
        
    case 'checkout':
        safeRequire(BASE_PATH . '/src/Controllers/OrderController.php');
        $controller = new \App\Controllers\OrderController();
        $controller->checkout();
        break;
        
    case 'process-checkout':
        safeRequire(BASE_PATH . '/src/Controllers/OrderController.php');
        $controller = new \App\Controllers\OrderController();
        $controller->processCheckout();
        break;
        
    case 'api/categories':
        safeRequire(BASE_PATH . '/src/Controllers/ApiController.php');
        $controller = new \App\Controllers\ApiController();
        $controller->categories();
        break;
        
    case 'api/products':
        safeRequire(BASE_PATH . '/src/Controllers/ApiController.php');
        $controller = new \App\Controllers\ApiController();
        $controller->products();
        break;
        
    case 'api/captcha':
        safeRequire(BASE_PATH . '/src/Controllers/CaptchaController.php');
        $controller = new \App\Controllers\CaptchaController();
        $controller->generate();
        break;
        
    case 'api/orders':
        safeRequire(BASE_PATH . '/src/Controllers/ManagerController.php');
        $controller = new \App\Controllers\ManagerController();
        $controller->show();
        break;
        
    // Manager routes
    case 'manager-users':
        safeRequire(BASE_PATH . '/src/Controllers/ManagerController.php');
        $controller = new \App\Controllers\ManagerController();
        $controller->users();
        break;
        
    case 'manager-orders':
        safeRequire(BASE_PATH . '/src/Controllers/ManagerController.php');
        $controller = new \App\Controllers\ManagerController();
        $controller->orders();
        break;
        
    case 'update-user-role':
        safeRequire(BASE_PATH . '/src/Controllers/ManagerController.php');
        $controller = new \App\Controllers\ManagerController();
        $controller->updateUserRole();
        break;
        
    case 'delete-user':
        safeRequire(BASE_PATH . '/src/Controllers/ManagerController.php');
        $controller = new \App\Controllers\ManagerController();
        $controller->deleteUser();
        break;
        
    case 'reset-user-password':
        safeRequire(BASE_PATH . '/src/Controllers/ManagerController.php');
        $controller = new \App\Controllers\ManagerController();
        $controller->resetUserPassword();
        break;
        
    // Review routes
    case 'review':
        safeRequire(BASE_PATH . '/src/Controllers/ReviewController.php');
        $controller = new \App\Controllers\ReviewController();
        $controller->showReviewForm();
        break;
        
    case 'review-submit':
        safeRequire(BASE_PATH . '/src/Controllers/ReviewController.php');
        $controller = new \App\Controllers\ReviewController();
        $controller->submitReview();
        break;
        
    case 'review-delete':
        safeRequire(BASE_PATH . '/src/Controllers/ReviewController.php');
        $controller = new \App\Controllers\ReviewController();
        $controller->deleteReview();
        break;
        
    // Newsletter route
    case 'newsletter-subscribe':
        safeRequire(BASE_PATH . '/src/Controllers/NewsletterController.php');
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
        $viewLoaded = safeRequire(BASE_PATH . '/src/Helpers/View.php');
        $errorControllerLoaded = safeRequire(BASE_PATH . '/src/Controllers/ErrorController.php');
        
        if ($viewLoaded && $errorControllerLoaded) {
            $controller = new \App\Controllers\ErrorController();
            $controller->notFound();
        } else {
            // Fallback error message if View or ErrorController can't be loaded
            echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; font-family: sans-serif;">';
            echo '<h1>Page Not Found</h1>';
            echo '<p>The requested page could not be found, and error handling components are missing.</p>';
            echo '<p><a href="home" style="color: #721c24;">Return to Home</a></p>';
            echo '</div>';
        }
        break;
} 