<?php
// Remove ViteAssets helper include
// require_once __DIR__ . '/../Helpers/ViteAssets.php'; 

// Prevent direct access to this template file
if (!defined('BASE_PATH')) {
    header('HTTP/1.0 403 Forbidden');
    echo '<h1>403 Forbidden</h1>';
    echo '<p>You are not allowed to access this file directly.</p>';
    echo '<p><a href="../public/index.php?route=register">Go to Registration Page</a></p>';
    exit;
}

// Retrieve CSRF token for React component
$csrfToken = htmlspecialchars($_SESSION['csrf_token'] ?? '');

// Find the built JavaScript file - DIRECT PATH APPROACH
$jsFile = 'dist/assets/main-BmDtgFI1.js';

// Keeping the dynamic detection as a fallback
if (!file_exists(__DIR__ . '/../../public/' . $jsFile)) {
    $jsFile = '';
    $possibleDirs = [
        __DIR__ . '/../../public/dist/assets',
        __DIR__ . '/../dist/assets',
        __DIR__ . '/../../dist/assets',
        __DIR__ . '/../../public/assets',
        __DIR__ . '/../../assets'
    ];

    foreach ($possibleDirs as $distDir) {
        if (is_dir($distDir)) {
            $files = glob($distDir . '/main-*.js'); // Look for main-[hash].js
            if (!empty($files)) {
                // Get the first match and make it relative to the public root
                $fileName = basename($files[0]);
                // Try to determine the correct URL path
                if (strpos($distDir, '/public/') !== false) {
                    $jsFile = str_replace('/public', '', $distDir);
                    $jsFile = str_replace(__DIR__ . '/../..', '', $jsFile) . '/' . $fileName;
                } else {
                    $jsFile = '/dist/assets/' . $fileName; 
                }
                break;
            }
        }
    }
}

// Debug output to help locate the files
if (empty($jsFile)) {
    error_log('Registration page: Could not find main-*.js in any of the expected directories');
} else {
    error_log('Registration page: Found JS file at ' . $jsFile);
}
// Note: This doesn't handle associated CSS files if the build outputs them separately.
// A more robust solution would parse manifest.json.

// Check if React is loaded
$reactAvailable = !empty($jsFile);
?>

<?php if (!empty($error)): // Display any *server-side* errors (e.g., from previous attempts) ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- React registration form - HIDDEN FOR NOW -->
<div id="react-register-form" data-csrf-token="<?= $csrfToken ?>" style="display: none;"></div>

<!-- TEMPORARILY DISABLE REACT LOADING to use HTML form only -->
<?php /*
<!-- Load the API proxy script to fix URL paths in the React component -->
<script src="register-proxy.js"></script>

<!-- Load the pre-built React asset if available -->
<?php if ($reactAvailable): ?>
    <script type="module" src="<?= htmlspecialchars($jsFile) ?>"></script>
<?php endif; ?>
*/ ?>

<!-- HTML Fallback registration form - ALWAYS shown now -->
<div id="html-register-form">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">Create an Account</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= \App\Helpers\View::url('register-post') ?>" class="needs-validation" novalidate>
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        
                        <!-- Name field -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                value="<?= htmlspecialchars($name ?? '') ?>" required>
                            <div class="invalid-feedback">Name must contain only letters and spaces (2-60 characters)</div>
                        </div>
                        
                        <!-- Phone field -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                value="<?= htmlspecialchars($phone ?? '') ?>" 
                                pattern="[0-9]{10}" required>
                            <div class="invalid-feedback">Please enter a valid 10-digit phone number</div>
                            <div class="form-text">Format: 10 digits with no spaces or dashes</div>
                        </div>
                        
                        <!-- Email field -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                value="<?= htmlspecialchars($email ?? '') ?>" required>
                            <div class="invalid-feedback">Please enter a valid email address</div>
                        </div>
                        
                        <!-- Password field -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                minlength="8" required>
                            <div class="invalid-feedback">Password must be at least 8 characters with at least one letter and one number</div>
                            <div class="form-text">Must contain at least 8 characters with letters and numbers</div>
                        </div>
                        
                        <!-- Submit button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Create Account</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0">Already have an account? <a href="<?= \App\Helpers\View::url('login') ?>">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add form validation script -->
<script src="js/form-validation.js"></script>

<!-- Script to check if React loads and show fallback if needed - NOT NEEDED NOW -->
<?php /*
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if React component rendered after a short delay
    setTimeout(function() {
        const reactForm = document.getElementById('react-register-form');
        const htmlForm = document.getElementById('html-register-form');
        
        // If React component is empty after 1 second, show HTML form
        if (reactForm && reactForm.childElementCount === 0 && htmlForm) {
            htmlForm.style.display = 'block';
            console.log('React form not loaded, showing HTML fallback');
        }
    }, 1000);
});
</script>
*/ ?>

<?php 
/*
// Removed Vite Dev Server logic
<?= vite_react_refresh_runtime() // Only outputs in dev mode ?>
<?php 
    // This needs to match the entry point in vite.config.js
    $entryPoint = 'src/react/main.jsx'; 
    
    $isDev = file_exists(__DIR__ . '/../../public/hot');

    if ($isDev) {
        // In development, load the Vite client and the entry point
        echo '<script type="module" src="' . VITE_HOST . '/@vite/client"></script>';
        echo '<script type="module" src="' . VITE_HOST . '/' . $entryPoint . '"></script>';
    } else {
        // In production, load assets from the manifest
        echo vite_asset($entryPoint);
    }
?> 
*/
?> 