<!-- Page Header Section -->
<div class="auth-header position-relative mb-4 text-center">
    <h1 class="fw-bold mb-2">Welcome Back</h1>
    <p class="lead text-muted">Log in to your account to access your orders</p>
</div>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-primary text-white p-3">
                <div class="d-flex align-items-center">
                    <i class="bi bi-shield-lock fs-4 me-2"></i>
                    <h2 class="h5 fw-bold mb-0">Secure Login</h2>
                </div>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                <?php endif; ?>
                
                <form action="login-post" method="post" id="login-form" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-medium">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="<?= htmlspecialchars($email ?? '') ?>" placeholder="your@email.com">
                        </div>
                        <div class="error-message text-danger mt-1 small"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label fw-medium">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="error-message text-danger mt-1 small"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="captcha" class="form-label fw-medium">Security Verification</label>
                        <div class="input-group">
                            <div class="d-flex align-items-center bg-light rounded p-2 me-2" style="flex: 0 0 auto;">
                                <img src="/captcha.php" id="captcha-image" alt="CAPTCHA" class="captcha-img"
                                     style="height: 50px; width: 150px; border-radius: 3px; cursor: pointer;">
                                <button type="button" id="refresh-captcha" class="btn btn-sm btn-link text-primary ms-1" 
                                        title="Refresh CAPTCHA">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                            <input type="text" class="form-control" id="captcha" name="captcha" required placeholder="Enter code">
                        </div>
                        <div class="error-message text-danger mt-1 small"></div>
                        <small class="text-muted mt-1 d-block">Enter the characters shown in the image</small>
                    </div>
                    
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary py-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login to Your Account
                        </button>
                    </div>
                    
                    <div class="text-center">
                        <p class="mb-0">Don't have an account? <a href="register" class="text-primary fw-medium">Register Now</a></p>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Security Info -->
        <div class="mt-4 text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
                <i class="bi bi-shield-check text-primary me-2"></i>
                <span class="small text-muted">Secure Authentication</span>
            </div>
            <p class="small text-muted mb-0">Your information is protected using secure connection</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Captcha refresh functionality
    const captchaImage = document.getElementById('captcha-image');
    const refreshButton = document.getElementById('refresh-captcha');
    
    const refreshCaptcha = function() {
        captchaImage.src = '/captcha.php?' + new Date().getTime();
    };
    
    // Initial load not needed as the image is loaded via src attribute
    
    if (refreshButton) {
        refreshButton.addEventListener('click', function(event) {
            event.preventDefault();
            refreshCaptcha();
        });
    }
    
    if (captchaImage) {
        captchaImage.addEventListener('click', refreshCaptcha);
    }
    
    // Form validation
    const form = document.getElementById('login-form');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const captchaInput = document.getElementById('captcha');
    
    // Email validation
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            validateEmail(this);
        });
    }
    
    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            if (emailInput && !validateEmail(emailInput)) {
                isValid = false;
            }
            
            if (passwordInput && passwordInput.value.trim() === '') {
                showError(passwordInput, 'Please enter your password');
                isValid = false;
            }
            
            if (captchaInput && captchaInput.value.trim() === '') {
                showError(captchaInput, 'Please enter the verification code');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
    
    // Helper functions
    function validateEmail(input) {
        // Use a more permissive email validation that allows more special characters
        // This pattern matches most valid emails while still providing basic validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(input.value)) {
            showError(input, 'Please enter a valid email address');
            return false;
        } else {
            clearError(input);
            return true;
        }
    }
    
    function showError(input, message) {
        const errorElement = input.parentElement.parentElement.querySelector('.error-message');
        input.classList.add('is-invalid');
        if (errorElement) {
            errorElement.textContent = message;
        }
    }
    
    function clearError(input) {
        const errorElement = input.parentElement.parentElement.querySelector('.error-message');
        input.classList.remove('is-invalid');
        if (errorElement) {
            errorElement.textContent = '';
        }
    }
});
</script> 