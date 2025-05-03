<?php
// Simple redirect script for the cart/ directory
// Now that the main cart page is working, redirect directly to it

// Define path
$mainCartPath = '../index.php?route=cart';

// Perform direct redirect
header('Location: ' . $mainCartPath);
exit;
?> 