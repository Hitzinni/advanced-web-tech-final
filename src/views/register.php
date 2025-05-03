<?php
// Remove ViteAssets helper include
// require_once __DIR__ . '/../Helpers/ViteAssets.php'; 
// Retrieve CSRF token for React component
$csrfToken = htmlspecialchars($_SESSION['csrf_token'] ?? '');

// Find the built JavaScript file
$jsFile = '';
$distDir = __DIR__ . '/../../public/dist/assets';
if (is_dir($distDir)) {
    $files = glob($distDir . '/main-*.js'); // Look for main-[hash].js
    if (!empty($files)) {
        // Get the first match and make it relative to the public root
        $jsFile = '/dist/assets/' . basename($files[0]); 
    }
}
// Note: This doesn't handle associated CSS files if the build outputs them separately.
// A more robust solution would parse manifest.json.

?>

<?php if (!empty($error)): // Display any *server-side* errors (e.g., from previous attempts) ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Placeholder for the React component -->
<div id="react-register-form" data-csrf-token="<?= $csrfToken ?>"></div>

<!-- 
    Removed the original HTML form and the old <script> block 
    containing the plain JavaScript validation.
-->

<!-- Load the pre-built React asset -->
<?php if (!empty($jsFile)): ?>
    <script type="module" src="<?= htmlspecialchars($jsFile) ?>"></script>
<?php else: ?>
    <!-- Fallback or error message if build file not found -->
    <div class="alert alert-warning">Could not load registration form script. Please run the build process.</div>
<?php endif; ?>

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