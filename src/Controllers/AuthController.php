<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\View;
use App\Models\User;
use App\Helpers\Validators;
use App\Middleware\CSRFProtection;
use App\Middleware\RateLimiter;

/**
 * Authentication Controller
 * Handles user login, registration, logout, and password change processes.
 */
class AuthController
{
    /** @var User User model instance for database interactions. */
    private $userModel;
    
    /**
     * Constructor
     * Initializes the User model.
     */
    public function __construct()
    {
        // Instantiate the User model for use in controller methods
        $this->userModel = new User();
    }
    
    /**
     * Displays the login form.
     * Redirects to home if the user is already logged in.
     * Clears any previous login error messages from the session.
     *
     * @return void
     */
    public function loginForm(): void
    {
        // Check if the user is already logged in based on session data.
        if (isset($_SESSION['user_id'])) {
            // If logged in, redirect to the homepage.
            header('Location: /');
            exit;
        }
        
        // Set page metadata for the login view
        $pageTitle = 'Login to Your Account';
        $metaDescription = 'Login to access your account and place orders at our online grocery store.';
        
        // Render the 'login' view using the View helper
        View::output('login', [
            'pageTitle' => $pageTitle,
            'metaDescription' => $metaDescription,
            // Pass any existing login error from the session to the view
            'error' => $_SESSION['login_error'] ?? null
        ]);
        
        // Clear the login error from the session after displaying it (flash message behavior)
        unset($_SESSION['login_error']);
    }
    
    /**
     * Processes the login form submission.
     * Includes rate limiting, CSRF protection, CAPTCHA validation, input validation,
     * password verification, session management, and redirection.
     *
     * @return void
     */
    public function login(): void
    {
        // Apply rate limiting based on IP address to prevent brute-force attacks.
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; // Get user IP or default
        RateLimiter::throttle("login:{$ip}", 5, 60 * 60); // Allow 5 attempts per hour per IP
        
        // Verify the CSRF token submitted with the form.
        if (!CSRFProtection::verifyToken($_POST['csrf_token'] ?? null)) {
            // If token is invalid, set an error message and redirect back to login form.
            $this->setLoginError('Security token invalid. Please try again.');
            header('Location: /login');
            exit;
        }
        
        // Retrieve email, password, and captcha from POST data.
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $captcha = $_POST['captcha'] ?? '';
        
        // Validate the submitted CAPTCHA against the value stored in the session (case-insensitive).
        $expectedCaptcha = $_SESSION['captcha'] ?? null;
        if ($expectedCaptcha === null || strtolower((string)$expectedCaptcha) !== strtolower((string)$captcha)) {
            // If CAPTCHA fails, set error and redirect back.
            $this->setLoginError('CAPTCHA verification failed. Please try again.');
            header('Location: /login');
            exit;
        }
        
        // Remove the used CAPTCHA value from the session to prevent reuse.
        unset($_SESSION['captcha']);
        
        // Validate the format of the email and ensure password is not empty.
        if (!Validators::email($email) || empty($password)) {
            // If validation fails, set error and redirect back.
            $this->setLoginError('Invalid email or password format.');
            header('Location: /login');
            exit;
        }
        
        // Attempt to find the user by email using the User model.
        $user = $this->userModel->findByEmail($email);
        // Verify if user exists and if the provided password matches the stored hash.
        if (!$user || !password_verify($password, $user['password_hash'])) {
            // If credentials don't match, set error and redirect back.
            // Use a generic error message for security.
            $this->setLoginError('Invalid credentials. Please try again.');
            header('Location: /login');
            exit;
        }
        
        // --- Login Successful ---
        // Regenerate the session ID to prevent session fixation attacks.
        session_regenerate_id(true);
        // Store essential user information in the session.
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role']; // Store user role
        
        // Redirect the user to the homepage after successful login.
        header('Location: /');
        exit;
    }
    
    /**
     * Displays the registration form.
     * Redirects to home if the user is already logged in.
     * Passes any previous registration errors or submitted data (for repopulation) to the view.
     * Clears flash data (errors, submitted values) from the session.
     *
     * @return void
     */
    public function registerForm(): void
    {
        try {
            // Check if the user is already logged in.
            if (isset($_SESSION['user_id'])) {
                // Redirect to homepage if logged in.
                header('Location: /');
                exit;
            }
            
            // Set page metadata for the registration view.
            $pageTitle = 'Create an Account';
            $metaDescription = 'Register for an account to place orders at our online grocery store.';
            
            // CSRF token setup if not already present
            if (!isset($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            
            // Render the 'register' view.
            View::output('register', [
                'pageTitle' => $pageTitle,
                'metaDescription' => $metaDescription,
                // Pass any registration error from previous attempt.
                'error' => $_SESSION['register_error'] ?? null,
                // Pass previously submitted data (if any) to repopulate the form.
                'name' => $_SESSION['register_data']['name'] ?? null,
                'phone' => $_SESSION['register_data']['phone'] ?? null,
                'email' => $_SESSION['register_data']['email'] ?? null
            ]);
            
            // Clear registration flash data (error message and submitted values) from session.
            unset($_SESSION['register_error'], $_SESSION['register_data']);
        } catch (\Throwable $e) {
            // Log the error
            error_log('AuthController::registerForm - Unhandled exception: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            
            // Display a basic registration form when the template fails
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>Create an Account | Online Grocery Store</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                    .container { max-width: 800px; margin: 0 auto; }
                    .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
                    .alert-danger { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
                    .card { border: 1px solid rgba(0,0,0,.125); border-radius: 4px; margin-bottom: 20px; }
                    .card-header { padding: 15px; background-color: #0d6efd; color: white; }
                    .card-body { padding: 15px; }
                    .card-footer { padding: 15px; background-color: rgba(0,0,0,.03); }
                    .form-group { margin-bottom: 15px; }
                    label { display: block; margin-bottom: 5px; }
                    input { width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; }
                    .btn { display: inline-block; padding: 8px 16px; background-color: #0d6efd; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
                    .form-text { color: #6c757d; font-size: 14px; margin-top: 5px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="alert alert-danger">
                        <strong>Notice:</strong> Using basic registration form due to a template rendering issue.
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h3 style="margin: 0;">Create an Account</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="index.php?route=register">
                                <input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">
                                
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" value="' . htmlspecialchars($_SESSION['register_data']['name'] ?? '') . '" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" value="' . htmlspecialchars($_SESSION['register_data']['phone'] ?? '') . '" pattern="[0-9]{10}" required>
                                    <div class="form-text">Format: 10 digits with no spaces or dashes</div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" value="' . htmlspecialchars($_SESSION['register_data']['email'] ?? '') . '" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" id="password" name="password" minlength="8" required>
                                    <div class="form-text">Must contain at least 8 characters with letters and numbers</div>
                                </div>
                                
                                <button type="submit" class="btn">Create Account</button>
                            </form>
                        </div>
                        <div class="card-footer" style="text-align: center;">
                            <p style="margin: 0;">Already have an account? <a href="index.php?route=login">Login here</a></p>
                        </div>
                    </div>
                    
                    <p><a href="index.php" class="btn">Back to Home</a></p>
                </div>
            </body>
            </html>';
        }
    }
    
    /**
     * Processes the registration form submission.
     * Includes CSRF validation, input validation (name, phone, email, password),
     * checks for existing email, password hashing, user creation via model,
     * session management, and redirection.
     *
     * @return void
     */
    public function register(): void
    {
        // Wrap the entire process in a try-catch block for unexpected errors.
        try {
            error_log("Registration process started"); // Log start
            
            // Verify the CSRF token.
            if (!CSRFProtection::verifyToken($_POST['csrf_token'] ?? null)) {
                error_log("CSRF validation failed");
                $this->setRegisterError('Security token invalid. Please try again.');
                header('Location: /register');
                exit;
            }
            
            // Retrieve registration data from POST request.
            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Log received data (excluding password itself).
            error_log("Registration attempt - Data received: " . json_encode([
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'password_length' => strlen($password)
            ]));
            
            // Store submitted data (except password) in session to repopulate form on error.
            $_SESSION['register_data'] = [
                'name' => $name,
                'phone' => $phone,
                'email' => $email
            ];
            
            // --- Input Validation ---
            if (!Validators::name($name)) {
                error_log("Invalid name format: {$name}");
                $this->setRegisterError('Invalid name format.');
                header('Location: /register');
                exit;
            }
            if (!Validators::phone($phone)) {
                error_log("Invalid phone format: {$phone}");
                $this->setRegisterError('Phone number must be exactly 10 digits.');
                header('Location: /register');
                exit;
            }
            if (!Validators::email($email)) {
                error_log("Invalid email format: {$email}");
                $this->setRegisterError('Invalid email format.');
                header('Location: /register');
                exit;
            }
            if (!Validators::password($password)) {
                error_log("Invalid password format");
                $this->setRegisterError('Password must be at least 8 characters with at least one letter and one number.');
                header('Location: /register');
                exit;
            }
            // --- End Input Validation ---
            
            // Check if the submitted email address is already in use.
            if ($this->userModel->isEmailTaken($email)) {
                error_log("Email already taken: {$email}");
                $this->setRegisterError('Email already in use. Please use a different email or login.');
                header('Location: /register');
                exit;
            }
            
            // --- User Creation ---
            // Hash the user's password securely using BCRYPT.
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            error_log("Password hashed, attempting to create user");
            
            // Attempt to create the user in the database via the User model.
            try {
                $userId = $this->userModel->create($name, $phone, $email, $passwordHash);
                error_log("User created successfully with ID: {$userId}"); // Log success
                
                // Clear the temporary registration data from the session.
                unset($_SESSION['register_data']);
                
                // Set a success message to be displayed on the login page.
                $_SESSION['login_success'] = 'Account created successfully. Please login.';
                
                // Redirect the user to the login page.
                header('Location: /login');
                exit;
            } catch (\Exception $e) {
                // Catch errors specifically from the user creation process (e.g., DB error).
                error_log("Failed to create user: " . $e->getMessage());
                $this->setRegisterError('An error occurred during registration. Please try again.');
                header('Location: /register');
                exit;
            }
        } catch (\Exception $e) {
            // Catch any other unexpected errors during the registration process.
            error_log("Unhandled exception in registration: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->setRegisterError('An unexpected error occurred. Please try again later.');
            header('Location: /register');
            exit;
        }
    }
    
    /**
     * Logs the user out by clearing the session data and redirecting to the login page.
     *
     * @return void
     */
    public function logout(): void
    {
        // Remove all session variables.
        session_unset();
        // Destroy the session.
        session_destroy();
        
        // Redirect the user to the login page.
        header('Location: /login');
        exit;
    }
    
    /**
     * Displays the change password form.
     * Requires the user to be logged in.
     * Clears any previous password change error messages from the session.
     *
     * @return void
     */
    public function changePasswordForm(): void
    {
        // Ensure the user is logged in before showing the form.
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        // Render the 'change-password' view.
        View::output('change-password', [
            'pageTitle' => 'Change Password',
            'metaDescription' => 'Update your account password.',
            // Pass any error from a previous attempt.
            'error' => $_SESSION['password_change_error'] ?? null
        ]);
        
        // Clear the password change error from the session (flash message).
        unset($_SESSION['password_change_error']);
    }
    
    public function changePassword(): void
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        // Validate CSRF token
        if (!CSRFProtection::verifyToken($_POST['csrf_token'] ?? null)) {
            $this->setPasswordChangeError('Security token invalid. Please try again.');
            header('Location: /change-password');
            exit;
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Check if all fields are filled
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->setPasswordChangeError('All fields are required.');
            header('Location: /change-password');
            exit;
        }
        
        // Check if new passwords match
        if ($newPassword !== $confirmPassword) {
            $this->setPasswordChangeError('New passwords do not match.');
            header('Location: /change-password');
            exit;
        }
        
        // Validate new password format
        if (!Validators::password($newPassword)) {
            $this->setPasswordChangeError('Password must be at least 8 characters with at least one letter and one number.');
            header('Location: /change-password');
            exit;
        }
        
        // Get current user data
        $userId = (int)$_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            // User not found (shouldn't happen normally)
            session_unset();
            session_destroy();
            header('Location: /login');
            exit;
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password_hash'])) {
            $this->setPasswordChangeError('Current password is incorrect.');
            header('Location: /change-password');
            exit;
        }
        
        // Hash the new password
        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Update the password in the database
        try {
            $this->userModel->updatePassword($userId, $newPasswordHash);
            
            // Set success message
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'Your password has been updated successfully.'
            ];
            
            // Redirect to home page
            header('Location: /');
            exit;
        } catch (\Exception $e) {
            error_log("Failed to update password: " . $e->getMessage());
            $this->setPasswordChangeError('An error occurred while updating your password. Please try again.');
            header('Location: /change-password');
            exit;
        }
    }
    
    private function setLoginError(string $message): void
    {
        $_SESSION['login_error'] = $message;
    }
    
    private function setRegisterError(string $message): void
    {
        $_SESSION['register_error'] = $message;
    }
    
    private function setPasswordChangeError(string $message): void
    {
        $_SESSION['password_change_error'] = $message;
    }
}