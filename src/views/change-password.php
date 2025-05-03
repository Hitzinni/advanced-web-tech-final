<!-- Page Header Section -->
<div class="category-header position-relative mb-4 bg-primary text-white p-4 rounded shadow">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h1 class="fw-bold mb-2"><i class="bi bi-shield-lock me-2"></i>Change Password</h1>
            <p class="lead mb-0">Update your account password</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="home" class="text-white">Home</a></li>
                    <li class="breadcrumb-item active text-white-50" aria-current="page">Change Password</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?> alert-dismissible fade show mb-4" role="alert">
        <?= $_SESSION['flash_message']['text'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light py-3">
                <h2 class="h5 mb-0"><i class="bi bi-key me-2"></i>Update Your Password</h2>
            </div>
            <div class="card-body p-4">
                <form action="process-password-change" method="post" id="change-password-form" class="needs-validation" novalidate>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger mb-3">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Please enter your current password.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Password must be at least 8 characters long with at least one letter and one number.</div>
                        <div class="form-text">At least 8 characters, including at least one letter and one number.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Passwords do not match.</div>
                    </div>
                    
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-shield-check me-1"></i>Update Password
                        </button>
                        <a href="home" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Return to Home
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card bg-light mb-4">
            <div class="card-body">
                <h3 class="h5 mb-3"><i class="bi bi-info-circle me-2"></i>Password Requirements</h3>
                <ul class="mb-0">
                    <li>At least 8 characters long</li>
                    <li>Must contain at least one letter</li>
                    <li>Must contain at least one number</li>
                    <li>Special characters are allowed but not required</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
    field.setAttribute('type', type);
    
    // Update the eye icon
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');
    icon.classList.toggle('bi-eye');
    icon.classList.toggle('bi-eye-slash');
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('change-password-form');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Check if passwords match
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match.');
            isValid = false;
        } else {
            confirmPassword.setCustomValidity('');
        }
        
        // Validate password complexity
        const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d).{8,}$/;
        if (!passwordRegex.test(newPassword.value)) {
            newPassword.setCustomValidity('Password must be at least 8 characters long with at least one letter and one number.');
            isValid = false;
        } else {
            newPassword.setCustomValidity('');
        }
        
        if (!form.checkValidity() || !isValid) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
});
</script> 