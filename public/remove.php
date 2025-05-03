<?php
// This is a redirect script for cart item removal
// It redirects from /remove.php to /cart/remove.php with the same parameters

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get all query parameters
$query = $_SERVER['QUERY_STRING'];

// Set a flash message to inform the user about the redirect
$_SESSION['flash_message'] = [
    'type' => 'info',
    'text' => 'The cart removal link has been updated. You are being redirected to the correct location.'
];

// Log the redirect for debugging
error_log("Redirecting from /remove.php to /cart/remove.php with query: " . $query);

// Redirect to the correct URL
header("Location: /cart/remove.php?" . $query);
exit; 