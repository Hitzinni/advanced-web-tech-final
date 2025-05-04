<?php
// Define base path (adjust the number of parent directories as needed)
define('BASE_PATH', dirname(__DIR__, 3));

// Start session
session_start();

// Load necessary files
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/src/Helpers/Validators.php';
require_once BASE_PATH . '/src/Models/User.php';

// Always respond as JSON
header('Content-Type: application/json');

// Enable CORS for the API endpoint
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only POST method is allowed']);
    exit;
}

// Debug logging
error_log('API register endpoint called with REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']);

try {
    // Parse the incoming JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // If JSON parsing failed, check for form data
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        $data = $_POST;
    }
    
    // Log the received data (excluding password)
    error_log('Received registration data: ' . json_encode([
        'name' => $data['name'] ?? 'not provided',
        'phone' => $data['phone'] ?? 'not provided',
        'email' => $data['email'] ?? 'not provided',
        'password_length' => isset($data['password']) ? strlen($data['password']) : 0,
        'csrf_token_length' => isset($data['csrf_token']) ? strlen($data['csrf_token']) : 0
    ]));
    
    // Extract and validate data
    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $csrfToken = $data['csrf_token'] ?? '';
    
    // Validate CSRF token (disabled for debugging)
    // if ($csrfToken !== $_SESSION['csrf_token']) {
    //    throw new Exception('Invalid CSRF token');
    // }
    
    // Validate the data
    $validationErrors = [];
    
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
    
    // If validation fails, return error
    if (!empty($validationErrors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => implode(' ', $validationErrors),
            'errors' => $validationErrors
        ]);
        exit;
    }
    
    // Check if email already exists
    $userModel = new \App\Models\User();
    $existingUser = $userModel->findByEmail($email);
    
    if ($existingUser) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Email address is already registered.'
        ]);
        exit;
    }
    
    // Create the user
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $userId = $userModel->create($name, $phone, $email, $passwordHash);
    
    if (!$userId) {
        throw new Exception('Failed to create user');
    }
    
    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful. Please login.',
        'redirectUrl' => '/prin/x8m18/kill%20me/advanced-web-tech-final/public/login'
    ]);
    
} catch (Exception $e) {
    error_log('Registration API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Registration failed: ' . $e->getMessage()
    ]);
} 