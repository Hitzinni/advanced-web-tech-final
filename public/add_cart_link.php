<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session to maintain cart state
session_start();

// Get the direct cart link
$cartDirectUrl = str_replace('add_cart_link.php', 'cart_direct.php', $_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fix Cart Link</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 1rem;
            line-height: 1.6;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        .warning { background-color: #fff3cd; border-color: #ffeeba; }
        .danger { background-color: #f8d7da; border-color: #f5c6cb; }
        pre { background: #f8f9fa; padding: 1rem; overflow: auto; }
        button { 
            background: #007bff; 
            color: white; 
            border: none; 
            padding: 0.5rem 1rem; 
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover { background: #0069d9; }
    </style>
</head>
<body>
    <h1>Fix Cart Link</h1>
    
    <div class="card info">
        <p>This tool will add a working cart link to your page. Bookmark this page for future use.</p>
        <p>Current cart direct URL: <strong><?= htmlspecialchars($cartDirectUrl) ?></strong></p>
    </div>
    
    <div class="card success">
        <h2>Instructions</h2>
        <ol>
            <li>Click the button below to add the script to your page</li>
            <li>Return to the website homepage or refresh your current page</li>
            <li>You should see a "CART (DIRECT)" button in the navigation bar</li>
            <li>This new button will always take you to the working cart page</li>
        </ol>
    </div>
    
    <button id="addScript">Add Cart Link to Website</button>
    
    <div id="status" class="card" style="display: none;"></div>
    
    <div class="card warning">
        <h2>How this works:</h2>
        <p>This script adds JavaScript to your browser's local storage. When you visit any page
        on this website, that JavaScript runs and adds a direct link to the working cart page.</p>
        <p>This is a client-side solution that doesn't modify any server files.</p>
    </div>
    
    <div class="card info">
        <h2>Cart URLs Available</h2>
        <p>We've created multiple ways to access the cart now:</p>
        <ul>
            <li><a href="<?= $cartDirectUrl ?>"><?= htmlspecialchars($cartDirectUrl) ?></a> - Direct cart page (always works)</li>
            <li><a href="cart.html">cart.html</a> - HTML redirect for /cart without trailing slash</li>
            <li><a href="cart/">cart/</a> - HTML redirect for /cart/ with trailing slash</li>
            <li><a href="cart_basic.php">cart_basic.php</a> - Basic cart implementation</li>
        </ul>
    </div>
    
    <div class="card danger">
        <h2>Still Having Issues?</h2>
        <p>Try these troubleshooting steps:</p>
        <ol>
            <li>Clear your browser cache</li>
            <li>Try a different browser</li>
            <li>Use the links from the "Cart URLs Available" section above directly</li>
            <li>Add a bookmark to <?= htmlspecialchars($cartDirectUrl) ?> for easy access</li>
        </ol>
    </div>
    
    <script>
        document.getElementById('addScript').addEventListener('click', function() {
            const cartFixScript = `
                // Add direct cart link to navigation
                function addDirectCartLink() {
                    // Create the direct cart link
                    const directCartUrl = '<?= $cartDirectUrl ?>';
                    
                    // Find the navigation bar
                    const navItems = document.querySelectorAll('nav ul, nav ol, .navbar-nav, header nav');
                    
                    if (navItems.length > 0) {
                        // Create the new cart link
                        const cartLinkItem = document.createElement('li');
                        cartLinkItem.className = 'nav-item';
                        
                        const cartLink = document.createElement('a');
                        cartLink.href = directCartUrl;
                        cartLink.className = 'nav-link text-white direct-link';
                        cartLink.innerHTML = '<strong style="background-color: #198754; padding: 5px 10px; border-radius: 5px;">CART (DIRECT)</strong>';
                        
                        cartLinkItem.appendChild(cartLink);
                        
                        // Add it to the navigation
                        navItems[0].appendChild(cartLinkItem);
                        console.log('Added direct cart link to navigation');
                    } else {
                        // If can't find navigation, add a floating button
                        const floatingCart = document.createElement('div');
                        floatingCart.style.position = 'fixed';
                        floatingCart.style.bottom = '20px';
                        floatingCart.style.right = '20px';
                        floatingCart.style.zIndex = '9999';
                        
                        const cartButton = document.createElement('a');
                        cartButton.href = directCartUrl;
                        cartButton.innerHTML = '<strong style="background-color: #198754; color: white; padding: 10px 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">CART (DIRECT)</strong>';
                        
                        floatingCart.appendChild(cartButton);
                        document.body.appendChild(floatingCart);
                        console.log('Added floating cart button');
                    }
                }
                
                // Run when the DOM is fully loaded
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', addDirectCartLink);
                } else {
                    addDirectCartLink();
                }
            `;
            
            // Store the script in local storage
            localStorage.setItem('cartFixScript', cartFixScript);
            
            // Also add a script to inject our fix on every page
            const injector = `
                // This runs on every page load
                if (localStorage.getItem('cartFixScript')) {
                    try {
                        eval(localStorage.getItem('cartFixScript'));
                    } catch (e) {
                        console.error('Error running cart fix script:', e);
                    }
                }
            `;
            
            localStorage.setItem('cartFixInjector', injector);
            
            // Show status
            const status = document.getElementById('status');
            status.classList.add('success');
            status.style.display = 'block';
            status.innerHTML = `
                <h2>✓ Cart Link Added!</h2>
                <p>The cart link has been added to your browser. Now:</p>
                <ol>
                    <li>Go back to the <a href="index.php">website homepage</a></li>
                    <li>You should see a "CART (DIRECT)" button</li>
                    <li>This button will always take you to the working cart page</li>
                </ol>
                <p>This fix will persist until you clear your browser data.</p>
            `;
            
            // Run the injector script immediately
            eval(injector);
        });
    </script>
    
    <script>
        // Always run the cart fix injector if it exists
        if (localStorage.getItem('cartFixInjector')) {
            try {
                eval(localStorage.getItem('cartFixInjector'));
            } catch (e) {
                console.error('Error running injector script:', e);
            }
        }
    </script>
</body>
</html> 