<?php
declare(strict_types=1);

// ENHANCED DEBUG MODE
// This will show critical server information and PHP errors
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Debug output function
function debug($data, $title = null) {
    echo '<div style="background: #f0f8ff; border: 1px solid #ccc; padding: 10px; margin: 10px; border-radius: 5px; font-family: monospace;">';
    if ($title) {
        echo "<h3>$title</h3>";
    }
    if (is_array($data) || is_object($data)) {
        echo '<pre>' . print_r($data, true) . '</pre>';
    } else {
        echo '<pre>' . htmlspecialchars((string)$data) . '</pre>';
    }
    echo '</div>';
}

// Always show basic debug information if ?server_info is in URL
if (isset($_GET['server_info'])) {
    echo '<h1>Server Information</h1>';
    debug($_SERVER, 'SERVER Variables');
    
    // Check if user constants exist before trying to access them
    $constants = get_defined_constants(true);
    if (isset($constants['user'])) {
        debug($constants['user'], 'User Constants');
    } else {
        debug([], 'No User Constants Defined');
    }
    
    debug(getcwd(), 'Current Directory');
    
    // Check if BASE_PATH is defined before trying to use it
    if (defined('BASE_PATH')) {
        debug(BASE_PATH, 'BASE_PATH Constant');
    } else {
        debug('Not defined', 'BASE_PATH Constant');
    }
    
    exit;
}

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
    $message = $exception->getMessage();
    $file = $exception->getFile();
    $line = $exception->getLine();
    $trace = $exception->getTraceAsString();
    
    // Log detailed exception info
    error_log("Uncaught Exception: $message in $file on line $line");
    error_log("Stack trace: $trace");
    
    // Display a user-friendly error page
    http_response_code(500);
    
    // If we're in debug mode, show detailed error
    if (isset($_GET['debug']) && $_GET['debug'] === 'exceptions') {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; font-family: sans-serif;">';
        echo '<h1>Application Error</h1>';
        echo "<p><strong>Exception:</strong> " . htmlspecialchars($message) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($file) . "</p>";
        echo "<p><strong>Line:</strong> $line</p>";
        echo "<p><strong>Stack Trace:</strong></p>";
        echo "<pre>" . htmlspecialchars($trace) . "</pre>";
        echo '</div>';
    } else {
        // User-friendly error page
        echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; font-family: sans-serif;">';
        echo '<h1>Sorry, something went wrong.</h1>';
        echo '<p>We encountered an error while processing your request. Please try again later.</p>';
        echo '<p><a href="home" style="color: #721c24;">Return to Home</a></p>';
        echo '<p><small>Add ?debug=exceptions to the URL to see error details</small></p>';
        echo '</div>';
    }
    exit;
});

// Helper function to check if a namespace\class exists
function classExists($className) {
    return class_exists($className, false);
}

// Add a safe require function to better handle missing files
function safeRequire($filePath) {
    if (file_exists($filePath)) {
        require_once $filePath;
        return true;
    } else {
        // Generate a more helpful error message
        $errorMsg = "File not found: $filePath";
        
        // Check if the directory exists but not the file
        $dir = dirname($filePath);
        if (is_dir($dir)) {
            $errorMsg .= "\nDirectory exists, but file is missing. Check file name case.";
            // List files in the directory to help debugging
            $files = scandir($dir);
            $errorMsg .= "\nFiles in directory:\n" . implode("\n", $files);
        } else {
            $errorMsg .= "\nDirectory does not exist: $dir";
        }
        
        // Log the error
        error_log($errorMsg);
        
        // Display error in browser
        echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; font-family: sans-serif;">';
        echo '<h1>Controller Not Found</h1>';
        echo "<p>The file <code>$filePath</code> does not exist.</p>";
        echo "<p>Current working directory: <code>" . getcwd() . "</code></p>";
        echo "<p>BASE_PATH: <code>" . BASE_PATH . "</code></p>";
        echo '<p>This could be due to:</p>';
        echo '<ul>';
        echo '<li>Case sensitivity issues in the file path</li>';
        echo '<li>Missing controller files in your project</li>';
        echo '<li>Directory permission issues</li>';
        echo '</ul>';
        
        if (isset($_GET['debug']) && $_GET['debug'] === 'files') {
            echo '<h3>Directory Details:</h3>';
            echo '<pre>' . htmlspecialchars($errorMsg) . '</pre>';
        }
        
        echo '</div>';
        return false;
    }
}

// Get route from URL path or query string for backward compatibility
$route = $_GET['route'] ?? '';

// If no route provided in the query string, try to get it from the URL path
if (empty($route)) {
    // Get the request URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    
    // Debug the raw request URI if debug parameter is set
    if (isset($_GET['debug'])) {
        debug($requestUri, 'Raw Request URI');
    }
    
    // Remove query string if present
    $uriPath = parse_url($requestUri, PHP_URL_PATH);
    
    // On the teaching server, the path might include the full directory structure
    // like /prin/x8m18/kill%20me/advanced-web-tech-final/public/products
    // We need to extract just the part after "public/"
    
    // First, lowercase for easier matching
    $lowercasePath = strtolower($uriPath);
    
    // EXTENSIVE DEBUGGING for routing issues
    error_log("========= ROUTE DEBUGGING =========");
    error_log("REQUEST_URI: " . $requestUri);
    error_log("URI Path: " . $uriPath);
    error_log("Lowercase Path: " . $lowercasePath);
    
    // Look for the public directory in the path
    if (strpos($lowercasePath, 'public') !== false) {
        // Find the position of 'public' in the path
        $publicPos = strpos($lowercasePath, 'public');
        // Get everything after 'public/' (adding 7 to skip over 'public/')
        $afterPublic = substr($uriPath, $publicPos + 7);
        $cleanPath = trim($afterPublic, '/');
        
        // Debug paths
        error_log("Public position: " . $publicPos);
        error_log("After Public: " . $afterPublic);
        error_log("Clean Path: " . $cleanPath);
    } else {
        // Not in public directory or public is not in path
        $cleanPath = trim($uriPath, '/');
        error_log("No 'public' in path. Clean Path: " . $cleanPath);
    }
    
    // Also handle direct access to index.php
    $cleanPath = preg_replace('#^index\.php/?#', '', $cleanPath);
    
    // Debug processed path if debug parameter is set
    if (isset($_GET['debug'])) {
        debug($cleanPath, 'Processed Route Path');
    }
    
    // If the path is empty, default to 'home'
    $route = $cleanPath ?: 'home';
    error_log("Final Route: " . $route);
    error_log("======== END ROUTE DEBUGGING ========");
}

// Debug final route if debug parameter is set
if (isset($_GET['debug'])) {
    debug($route, 'Final Route');
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
        
    case 'api/register':
        // API adapter for React registration
        // Forward the request to the regular registration handler
        if (safeRequire(BASE_PATH . '/src/Controllers/AuthController.php')) {
            // Set content type for API response
            header('Content-Type: application/json');
            
            try {
                // Convert JSON body to POST parameters if needed
                $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
                if (strpos($contentType, 'application/json') !== false) {
                    $json = file_get_contents('php://input');
                    $data = json_decode($json, true);
                    if ($data) {
                        $_POST = array_merge($_POST, $data);
                    }
                }
                
                // Initialize controller but don't call register() method yet
                $controller = new \App\Controllers\AuthController();
                
                // Validate inputs first
                $name = $_POST['name'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $csrfToken = $_POST['csrf_token'] ?? '';
                
                // For debugging
                error_log('API register route called with data: ' . json_encode([
                    'name' => $name,
                    'phone' => $phone,
                    'email' => $email,
                    'password_length' => strlen($password),
                    'csrf_token_length' => strlen($csrfToken),
                ]));
                
                // Store the current output buffer level
                $currentLevel = ob_get_level();
                
                // Start output buffering to capture any output/headers
                ob_start();
                
                // Call the registration method but intercept the result
                $success = false;
                try {
                    // Instead of directly calling register which might redirect,
                    // check if inputs are valid
                    // Validate the data
                    $validationErrors = [];
                    
                    // Import Validators class
                    require_once BASE_PATH . '/src/Helpers/Validators.php';
                    
                    if (!\App\Helpers\Validators::name($name)) {
                        $validationErrors[] = 'Invalid name format.';
                    }
                    if (!\App\Helpers\Validators::phone($phone)) {
                        $validationErrors[] = 'Phone number must be exactly 10 digits.';
                    }
                    if (!\App\Helpers\Validators::email($email)) {
                        $validationErrors[] = 'Invalid email format.';
                    }
                    if (!\App\Helpers\Validators::password($password)) {
                        $validationErrors[] = 'Password must be at least 8 characters with at least one letter and one number.';
                    }
                    
                    if (empty($validationErrors)) {
                        // If validation passes, check for existing email
                        require_once BASE_PATH . '/src/Models/User.php';
                        $userModel = new \App\Models\User();
                        $existingUser = $userModel->findByEmail($email);
                        
                        if ($existingUser) {
                            $validationErrors[] = 'Email address is already registered.';
                        } else {
                            // Create the user
                            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                            $userId = $userModel->create($name, $phone, $email, $passwordHash);
                            
                            if ($userId) {
                                $success = true;
                            } else {
                                $validationErrors[] = 'Database error when creating user.';
                            }
                        }
                    }
                    
                    // Clean output buffer
                    ob_end_clean();
                    
                    if ($success) {
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Registration successful. Please login.',
                            'redirectUrl' => './login'
                        ]);
                    } else {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false, 
                            'message' => implode(' ', $validationErrors),
                            'errors' => $validationErrors
                        ]);
                    }
                } catch (\Exception $e) {
                    ob_end_clean();
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
                }
            } catch (\Exception $e) {
                // Return error response
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
            }
            exit;
        }
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