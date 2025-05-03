// Debug script to help diagnose navigation issues
document.addEventListener('DOMContentLoaded', function() {
    // Log all link clicks for debugging
    document.addEventListener('click', function(e) {
        const target = e.target.closest('a');
        if (target) {
            console.log('Link clicked:', {
                href: target.getAttribute('href'),
                id: target.id,
                class: target.className,
                text: target.textContent.trim()
            });
        }
    });
    
    // Add a helper function to redirect in case of issues
    window.navigateTo = function(path) {
        console.log('Manual navigation to:', path);
        window.location.href = path;
    };
    
    // Add debug buttons at the bottom of the page
    const debugContainer = document.createElement('div');
    debugContainer.style.position = 'fixed';
    debugContainer.style.bottom = '10px';
    debugContainer.style.right = '10px';
    debugContainer.style.backgroundColor = 'rgba(0,0,0,0.7)';
    debugContainer.style.padding = '10px';
    debugContainer.style.borderRadius = '5px';
    debugContainer.style.color = 'white';
    debugContainer.style.zIndex = '9999';
    
    const homeButton = document.createElement('button');
    homeButton.textContent = 'Home';
    homeButton.style.marginRight = '5px';
    homeButton.onclick = function() { navigateTo('/home'); };
    
    const productsButton = document.createElement('button');
    productsButton.textContent = 'Products';
    productsButton.style.marginRight = '5px';
    productsButton.onclick = function() { navigateTo('/products'); };
    
    const loginButton = document.createElement('button');
    loginButton.textContent = 'Login';
    loginButton.style.marginRight = '5px';
    loginButton.onclick = function() { navigateTo('/login'); };
    
    const registerButton = document.createElement('button');
    registerButton.textContent = 'Register';
    registerButton.onclick = function() { navigateTo('/register'); };
    
    debugContainer.appendChild(homeButton);
    debugContainer.appendChild(productsButton);
    debugContainer.appendChild(loginButton);
    debugContainer.appendChild(registerButton);
    
    document.body.appendChild(debugContainer);
    
    console.log('Debug tools loaded - now watching for link clicks');
}); 