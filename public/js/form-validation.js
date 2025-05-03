document.addEventListener('DOMContentLoaded', function() {
    // Get login form (registration form handled by React)
    const loginForm = document.getElementById('login-form');
    
    // Regex patterns for validation
    const patterns = {
        // name: /^[A-Za-z\s]{2,60}$/, // Handled by React
        // phone: /^\d{10}$/, // Handled by React
        email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
        // password: /^(?=.*[A-Za-z])(?=.*\d).{8,}$/ // Handled by React, but keep email regex for login
    };
    
    // Functions to validate each field
    // const validateName = (value) => { ... }; // Removed
    // const validatePhone = (value) => { ... }; // Removed
    
    const validateEmail = (value) => {
        return patterns.email.test(value);
    };
    
    // const validatePassword = (value) => { ... }; // Removed (only simple check needed for login)
    
    // Show error message
    const showError = (input, message) => {
        const formGroup = input.parentElement;
        // Adjust selector if error message div is structured differently in login form
        const errorElement = formGroup.querySelector('.invalid-feedback') || formGroup.querySelector('.error-message'); 
        
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.setAttribute('aria-live', 'polite');
            input.classList.add('is-invalid');
        } else {
             console.warn('Could not find error element for', input);
        }
    };
    
    // Clear error message
    const clearError = (input) => {
        const formGroup = input.parentElement;
        const errorElement = formGroup.querySelector('.invalid-feedback') || formGroup.querySelector('.error-message');
        
        if (errorElement) {
            errorElement.textContent = '';
            input.classList.remove('is-invalid');
        }
    };
    
    // Add validation listeners to registration form - REMOVED
    // if (registerForm) { ... } 
    
    // CAPTCHA refresh functionality (keep if used on login or other forms)
    const captchaImage = document.getElementById('captcha-image');
    const refreshButton = document.getElementById('refresh-captcha');
    
    if (refreshButton && captchaImage) {
        refreshButton.addEventListener('click', function(event) {
            event.preventDefault();
            // Ensure the path is correct for where captcha is displayed
            // It might be /captcha.php or /api/captcha depending on the form
            const captchaSrc = captchaImage.src.split('?')[0]; 
            captchaImage.src = captchaSrc + '?t=' + new Date().getTime();
        });
    }
    
    // Login form validation
    if (loginForm) {
        const emailInput = loginForm.querySelector('input[name="email"]');
        const passwordInput = loginForm.querySelector('input[name="password"]');
        const captchaInput = loginForm.querySelector('input[name="captcha"]'); // Assuming login has captcha
        
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                // Live validation for login email can be simple or use regex
                if (!validateEmail(this.value)) {
                    showError(this, 'Please enter a valid email address');
                } else {
                    clearError(this);
                }
            });
        }
        
        // Optional: Add live validation for other login fields if desired (e.g., captcha format)
        if (captchaInput) {
             captchaInput.addEventListener('input', function() {
                 if (this.value.trim().length === 0) { // Basic check: not empty
                     showError(this, 'Please enter the CAPTCHA code');
                 } else {
                     clearError(this);
                 }
             });
         }

         if (passwordInput) {
             passwordInput.addEventListener('input', function() {
                 if (this.value.trim().length === 0) { // Basic check: not empty
                     showError(this, 'Password cannot be empty');
                 } else {
                     clearError(this);
                 }
             });
         }
        
        // Form submission validation (client-side)
        loginForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            if (emailInput && !validateEmail(emailInput.value)) {
                showError(emailInput, 'Please enter a valid email address');
                isValid = false;
            }
            
            if (passwordInput && passwordInput.value.length < 1) {
                showError(passwordInput, 'Please enter your password');
                isValid = false;
            }
            
            // Check captcha only if the input exists
            if (captchaInput && captchaInput.value.length < 1) { 
                showError(captchaInput, 'Please enter the CAPTCHA code');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault(); // Prevent submission if client-side validation fails
            }
        });
    }
}); 