<!-- Modern Hero Section with Split Design -->
<div class="hero-section position-relative mb-5 rounded-4 overflow-hidden shadow">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Left Content Side -->
            <div class="col-lg-5">
                <div class="hero-content d-flex flex-column justify-content-center p-4 p-lg-5 h-100" style="background: linear-gradient(135deg, #2c3e50, #3498db);">
                    <div class="py-4 py-lg-3">
                        <h5 class="text-uppercase text-white-50 mb-3 letter-spacing-2 small fw-bold">Premium Quality</h5>
                        <h1 class="display-5 fw-bold text-white mb-3">Fresh Groceries, <br>Delivered Daily</h1>
                        <p class="lead text-white-50 mb-4">Farm-fresh produce and premium meats delivered to your doorstep</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="products" class="btn btn-light px-4 py-2 fw-medium">Shop Now</a>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                            <a href="register" class="btn btn-outline-light px-4 py-2">Join Now</a>
                            <?php else: ?>
                            <a href="order-receipt" class="btn btn-outline-light px-4 py-2">My Orders</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Image Side -->
            <div class="col-lg-7 d-none d-lg-block">
                <div class="hero-image position-relative h-100" style="background-image: url('images/hero-grocery.jpg'); background-size: cover; background-position: center; height: 380px;">
                    <!-- Special Promotion Badge (positioned to completely fill right side) -->
                    <div class="position-absolute" style="top: 0; right: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                        <div class="promo-badge bg-white text-dark rounded-3 p-4 shadow-lg" style="border: 4px solid #3498db; width: 95%; height: 95%; display: flex; flex-direction: column; justify-content: center;">
                            <div class="text-center mb-4">
                                <h3 class="text-primary mb-0" style="font-weight: 900; font-size: 35px; letter-spacing: -0.5px; text-transform: uppercase;">Special Deal</h3>
                                <div class="border-bottom border-primary border-2 w-50 mx-auto mt-2 mb-3"></div>
                            </div>
                            
                            <div class="row align-items-center g-4 mx-0">
                                <div class="col-lg-6">
                                    <div class="d-flex flex-column">
                                        <!-- Food images stack -->
                                        <div class="d-flex mb-4 justify-content-center">
                                            <img src="images/vegetables-category.jpg" alt="Vegetables" class="img-fluid rounded-3 me-3" 
                                                 style="width: 140px; height: 140px; object-fit: cover; border: 4px solid #28a745; box-shadow: 0 8px 16px rgba(0,0,0,0.15);">
                                            <img src="images/fruits-category.jpg" alt="Fruits" class="img-fluid rounded-3" 
                                                 style="width: 140px; height: 140px; object-fit: cover; border: 4px solid #fd7e14; box-shadow: 0 8px 16px rgba(0,0,0,0.15);">
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            <img src="images/meat-category.jpg" alt="Meat" class="img-fluid rounded-3" 
                                                 style="width: 290px; height: 140px; object-fit: cover; border: 4px solid #dc3545; box-shadow: 0 8px 16px rgba(0,0,0,0.15);">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="text-center text-lg-start mt-3 mt-lg-0">
                                        <div style="color: #003366; font-weight: 800; font-size: 40px; line-height: 1.2;">
                                            2 Fruits &<br>
                                            2 Vegetables<br>
                                            and Any Meat
                                        </div>
                                        <div class="mt-4 d-flex align-items-center justify-content-center justify-content-lg-start">
                                            <span class="me-2" style="font-size: 30px; font-weight: 700; color: #003366;">for only</span>
                                            <span class="badge bg-danger p-3" style="font-size: 50px; transform: rotate(-5deg);">$10</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Overlay with Featured Products (bottom-right) -->
                    <div class="position-absolute bottom-0 end-0 m-4">
                        <div class="d-flex">
                            <div class="featured-badge bg-white text-dark rounded-pill px-3 py-2 shadow-sm me-2 d-flex align-items-center">
                                <span class="badge rounded-circle bg-danger p-2 me-2"><i class="bi bi-tag-fill small"></i></span>
                                <span class="small fw-bold">25% OFF Weekends</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container mb-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="feature-card text-center p-4 h-100 rounded-4 shadow-sm border-top border-primary border-3 hover-lift">
                <div class="feature-icon mb-3 text-primary">
                    <i class="bi bi-truck fs-1"></i>
                </div>
                <h3 class="h5 mb-3">Fast Delivery</h3>
                <p class="mb-0 text-muted">Same-day delivery available on orders placed before noon</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card text-center p-4 h-100 rounded-4 shadow-sm border-top border-primary border-3 hover-lift">
                <div class="feature-icon mb-3 text-primary">
                    <i class="bi bi-award fs-1"></i>
                </div>
                <h3 class="h5 mb-3">Quality Guaranteed</h3>
                <p class="mb-0 text-muted">We select only the freshest products for our customers</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card text-center p-4 h-100 rounded-4 shadow-sm border-top border-primary border-3 hover-lift">
                <div class="feature-icon mb-3 text-primary">
                    <i class="bi bi-shield-check fs-1"></i>
                </div>
                <h3 class="h5 mb-3">100% Secure Checkout</h3>
                <p class="mb-0 text-muted">Your payment information is always secure with us</p>
            </div>
        </div>
    </div>
</div>

<!-- Categories Section with Hover Effects -->
<div class="container mb-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="section-title position-relative pb-2 mb-4">Shop By Category</h2>
            <p class="lead text-muted">Explore our wide selection of fresh products</p>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="category-card position-relative rounded overflow-hidden shadow">
                <img src="images/vegetables-category.jpg" class="img-fluid category-img w-100" alt="Vegetables">
                <div class="category-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-end p-4 text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                    <h3 class="h4 mb-2">Fresh Vegetables</h3>
                    <p class="mb-3">Locally-sourced and organic options</p>
                    <a href="products?category=Vegetables" class="btn btn-sm btn-success stretched-link">Shop Vegetables</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="category-card position-relative rounded overflow-hidden shadow">
                <img src="images/fruits-category.jpg" class="img-fluid category-img w-100" alt="Fruits">
                <div class="category-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-end p-4 text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                    <h3 class="h4 mb-2">Fresh Fruits</h3>
                    <p class="mb-3">Sweet and juicy seasonal fruits</p>
                    <a href="products?category=Fruits" class="btn btn-sm btn-success stretched-link">Shop Fruits</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="category-card position-relative rounded overflow-hidden shadow">
                <img src="images/meat-category.jpg" class="img-fluid category-img w-100" alt="Quality Meats">
                <div class="category-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-end p-4 text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                    <h3 class="h4 mb-2">Quality Meats</h3>
                    <p class="mb-3">Premium cuts and selections</p>
                    <a href="products?category=Meat" class="btn btn-sm btn-success stretched-link">Shop Meats</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="category-card position-relative rounded overflow-hidden shadow">
                <img src="images/bakery-category.jpg" class="img-fluid category-img w-100" alt="Bakery">
                <div class="category-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-end p-4 text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                    <h3 class="h4 mb-2">Fresh Bakery</h3>
                    <p class="mb-3">Freshly baked bread and pastries</p>
                    <a href="products?category=Bakery" class="btn btn-sm btn-success stretched-link">Shop Bakery</a>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="category-card position-relative rounded overflow-hidden shadow">
                <img src="images/dairy-category.jpg" class="img-fluid category-img w-100" alt="Dairy Products">
                <div class="category-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-end p-4 text-white" style="background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);">
                    <h3 class="h4 mb-2">Dairy Products</h3>
                    <p class="mb-3">Fresh milk, cheese, and more</p>
                    <a href="products?category=Dairy" class="btn btn-sm btn-success stretched-link">Shop Dairy</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Special Offers Section -->
<div class="container-fluid bg-light py-5 mb-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="section-title position-relative pb-2 mb-4">Special Offers</h2>
                <p class="lead text-muted mb-0">Limited time deals you don't want to miss</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow offer-card">
                    <div class="row g-0">
                        <div class="col-4 d-flex align-items-center justify-content-center bg-success text-white p-3">
                            <div class="text-center">
                                <h3 class="display-4 mb-0 fw-bold">25%</h3>
                                <p class="mb-0">OFF</p>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h4 class="card-title mb-0">Weekend Special</h4>
                                    <span class="badge bg-danger">Hot Deal</span>
                                </div>
                                <p class="card-text">Get 25% off on all fresh vegetables this weekend! Stock up on nutritious produce for the week ahead.</p>
                                <a href="products?category=Vegetables" class="btn btn-outline-success">Shop Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow offer-card">
                    <div class="row g-0">
                        <div class="col-4 d-flex align-items-center justify-content-center bg-danger text-white p-3">
                            <div class="text-center">
                                <h3 class="display-4 mb-0 fw-bold">10%</h3>
                                <p class="mb-0">OFF</p>
                            </div>
                        </div>
                        <div class="col-8">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h4 class="card-title mb-0">First Order</h4>
                                    <span class="badge bg-success">New Users</span>
                                </div>
                                <p class="card-text">New to our store? Enjoy 10% off your first order when you create an account today!</p>
                                <?php if (!isset($_SESSION['user_id'])): ?>
                                <a href="register" class="btn btn-outline-danger">Sign Up</a>
                                <?php else: ?>
                                <a href="products" class="btn btn-outline-danger">Browse Products</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Testimonials Section -->
<div class="container mb-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="section-title position-relative pb-2 mb-4">What Our Customers Say</h2>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="mb-4">
                    <a href="/review" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-pencil-square me-1"></i>Write a Review
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="col-md-4 mb-4">
                    <div class="testimonial-card p-4 rounded shadow-sm h-100 position-relative">
                        <div class="mb-3 text-warning">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?= $i <= $review['rating'] ? '-fill' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="mb-4">"<?= htmlspecialchars($review['content']) ?>"</p>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <span><?= strtoupper(substr($review['user_name'], 0, 1)) ?></span>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($review['user_name']) ?></h6>
                                <small class="text-muted"><?= date('M j, Y', strtotime($review['created_at'])) ?></small>
                            </div>
                        </div>
                        <div class="testimonial-quote position-absolute text-success opacity-25" style="bottom: 10px; right: 10px;">
                            <i class="bi bi-quote fs-1"></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <div class="p-4 bg-light rounded">
                    <p class="mb-0">No reviews yet. Be the first to share your experience!</p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/review" class="btn btn-primary mt-3">Write a Review</a>
                    <?php else: ?>
                        <a href="/login" class="btn btn-primary mt-3">Login to Write a Review</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Newsletter Section -->
<div class="container-fluid bg-success py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center text-lg-start mb-4 mb-lg-0">
                <h2 class="text-white mb-3">Subscribe to Our Newsletter</h2>
                <p class="text-white-50 mb-0">Stay updated with our latest offers, recipes, and healthy eating tips.</p>
            </div>
            <div class="col-lg-6">
                <?php if (isset($_SESSION['newsletter_success'])): ?>
                    <div class="alert alert-light">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <?= htmlspecialchars($_SESSION['newsletter_success']) ?>
                        <?php unset($_SESSION['newsletter_success']); ?>
                    </div>
                <?php elseif (isset($_SESSION['newsletter_error'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <?= htmlspecialchars($_SESSION['newsletter_error']) ?>
                        <?php unset($_SESSION['newsletter_error']); ?>
                    </div>
                <?php else: ?>
                    <form action="/newsletter-subscribe" method="post" class="d-flex flex-column flex-sm-row gap-2">
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="Your Email Address" required>
                        <button type="submit" class="btn btn-light btn-lg px-4">
                            <i class="bi bi-envelope-check me-2"></i>Subscribe
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom CSS for the homepage */
.text-shadow {
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 3px;
    background-color: #198754; /* Bootstrap success color */
}

.hover-lift {
    transition: transform 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-10px);
}

.category-img {
    height: 250px;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.category-card:hover .category-img {
    transform: scale(1.05);
}

.offer-card {
    transition: transform 0.3s ease;
}

.offer-card:hover {
    transform: translateY(-5px);
}

.testimonial-card {
    border-left: 4px solid #198754;
    transition: transform 0.3s ease;
}

.testimonial-card:hover {
    transform: translateY(-5px);
}
</style>

<!-- Add Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css"> 