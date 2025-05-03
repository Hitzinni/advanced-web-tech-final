<?php
// Prevent direct access to this template file
if (!defined('BASE_PATH')) {
    header('HTTP/1.0 403 Forbidden');
    echo '<h1>403 Forbidden</h1>';
    echo '<p>You are not allowed to access this file directly.</p>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Online Grocery Store' ?></title>
    <meta name="description" content="<?= $metaDescription ?? 'Browse and order fresh vegetables and meats from our online grocery store.' ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= \App\Helpers\View::asset('css/styles.css') ?>">

    <!-- SEO Tags -->
    <meta property="og:title" content="<?= $pageTitle ?? 'Online Grocery Store' ?>">
    <meta property="og:description" content="<?= $metaDescription ?? 'Browse and order fresh vegetables and meats from our online grocery store.' ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://<?= htmlspecialchars($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
    <meta property="og:image" content="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?><?= \App\Helpers\View::asset('images/grocery-store.jpg') ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $pageTitle ?? 'Online Grocery Store' ?>">
    <meta name="twitter:description" content="<?= $metaDescription ?? 'Browse and order fresh vegetables and meats from our online grocery store.' ?>">
    <meta name="twitter:image" content="https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?><?= \App\Helpers\View::asset('images/grocery-store.jpg') ?>">

    <!-- Favicon -->
    <link rel="icon" href="<?= \App\Helpers\View::asset('favicon.ico') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= \App\Helpers\View::asset('images/apple-touch-icon.png') ?>">

    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? '' ?>">

    <!-- Structured Data - Organization -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "Grocery Store",
            "url": "https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?>",
            "logo": "https://<?= htmlspecialchars($_SERVER['HTTP_HOST']) ?><?= \App\Helpers\View::asset('images/logo.png') ?>",
            "description": "Browse and order fresh vegetables and meats from our online grocery store."
        }
    </script>
</head>

<body <?= isset($_SESSION['user_id']) ? 'data-authenticated="true"' : '' ?>>
    <!-- Blue header using the new color scheme -->
    <header class="bg-primary text-white py-2">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="<?= \App\Helpers\View::url('home') ?>" class="text-decoration-none">
                    <img src="<?= \App\Helpers\View::asset('images/logo.svg') ?>" alt="Grocery Store" height="40" class="d-inline-block align-top">
                </a>
                <nav>
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= \App\Helpers\View::url('home') ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= \App\Helpers\View::url('products') ?>">Browse Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= \App\Helpers\View::url('about') ?>">About Us</a>
                        </li>
                        <li class="nav-item">
                            <!-- Direct cart link as primary option - always visible -->
                            <a class="nav-link text-white" href="<?= \App\Helpers\View::url('cart') ?>" style="font-weight: bold;">
                                <i class="bi bi-cart3 me-1"></i>Cart
                            </a>
                        </li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <?= htmlspecialchars($_SESSION['email'] ?? 'Account') ?>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="<?= \App\Helpers\View::url('my-orders') ?>">My Orders</a>
                                    <a class="dropdown-item" href="<?= \App\Helpers\View::url('change-password') ?>">
                                        <i class="bi bi-shield-lock me-1"></i>Change Password
                                    </a>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'manager'): ?>
                                        <h6 class="dropdown-header">Admin Options</h6>
                                        <a class="dropdown-item text-danger" href="<?= \App\Helpers\View::url('manager-users') ?>">
                                            <i class="bi bi-person-gear me-1"></i>User Management
                                        </a>
                                        <a class="dropdown-item text-danger" href="<?= \App\Helpers\View::url('manager-orders') ?>">
                                            <i class="bi bi-box-seam me-1"></i>Order Management
                                        </a>
                                        <div class="dropdown-divider"></div>
                                    <?php endif; ?>
                                    <a class="dropdown-item" href="<?= \App\Helpers\View::url('logout') ?>">Logout</a>
                                </div>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link text-white direct-link" href="<?= \App\Helpers\View::url('login') ?>">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white direct-link" href="<?= \App\Helpers\View::url('register') ?>">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container py-4">
        <?php if (isset($flashMessage)): ?>
            <div class="alert alert-<?= $flashMessage['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flashMessage['text']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>

    <footer class="bg-dark text-white p-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Online Grocery Store</h5>
                    <p>The best selection of fresh vegetables and meats.</p>
                    <div>
                        <a href="<?= \App\Helpers\View::url('home') ?>" class="btn btn-sm btn-light me-2">Home</a>
                        <a href="<?= \App\Helpers\View::url('products') ?>" class="btn btn-sm btn-light me-2">Products</a>
                        <a href="<?= \App\Helpers\View::url('about') ?>" class="btn btn-sm btn-light me-2">About Us</a>
                        <a href="<?= \App\Helpers\View::url('cart') ?>" class="btn btn-sm btn-light me-2">Cart</a>
                        <a href="<?= \App\Helpers\View::url('login') ?>" class="btn btn-sm btn-light me-2">Login</a>
                        <a href="<?= \App\Helpers\View::url('register') ?>" class="btn btn-sm btn-light">Register</a>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <?= date('Y') ?> Advanced Web Technologies</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script src="<?= \App\Helpers\View::asset('assets/js/main.js') ?>"></script>
    <script src="<?= \App\Helpers\View::asset('js/catalog.js') ?>"></script>
    <script src="<?= \App\Helpers\View::asset('js/form-validation.js') ?>"></script>
</body>

</html>