// Register-Proxy.js - This file intercepts fetch requests to /api/register and redirects them
(function() {
    console.log("Registration proxy script loaded");
    
    // Store the original fetch function
    const originalFetch = window.fetch;
    
    // Override the fetch function
    window.fetch = function(url, options) {
        if (typeof url === 'string' && url.includes('/api/register')) {
            console.log("Intercepted registration API call to:", url);
            
            // Replace with the full path to your API endpoint - using HTTPS
            const newUrl = 'https://teach.scam.keele.ac.uk/prin/x8m18/advwebtec/advanced-web-tech-final/public/api/register';
            console.log("Redirecting to:", newUrl);
            
            // Call the original fetch with the new URL
            return originalFetch(newUrl, options);
        }
        
        // For all other requests, use the original fetch
        return originalFetch.apply(this, arguments);
    };
})(); 