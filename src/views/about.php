<!-- About Us Page Header -->
<div class="category-header position-relative mb-5 bg-primary text-white p-4 rounded shadow">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h1 class="fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>About Us</h1>
            <p class="lead mb-0">Learn about our mission, values, and the team behind our store</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="home" class="text-white">Home</a></li>
                    <li class="breadcrumb-item active text-white-50" aria-current="page">About Us</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Mission Section -->
<section class="mb-5">
    <div class="row align-items-center">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="position-relative">
                <img src="images/store/grocery-storefront.jpg" alt="Our Store" class="img-fluid rounded shadow-sm" style="object-fit: cover; height: 400px; width: 100%;" onerror="this.src='images/store/placeholder.jpg'">
                <div class="position-absolute bottom-0 end-0 p-3 bg-primary text-white rounded-top-left">
                    <span class="h5">Est. 2015</span>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <h2 class="display-6 fw-bold mb-4">Our Mission</h2>
            <p class="lead text-muted mb-4">
                At our grocery store, we're committed to providing fresh, high-quality products while supporting local farmers and sustainable practices.
            </p>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold">Quality Products</h5>
                            <p>We carefully select the freshest and highest quality items for our customers.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold">Local Sourcing</h5>
                            <p>We partner with local farmers to bring you the freshest produce.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold">Sustainability</h5>
                            <p>Our eco-friendly packaging and practices help protect the environment.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold">Community Support</h5>
                            <p>We're dedicated to giving back to the communities we serve.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="mb-5 py-5 bg-light rounded">
    <div class="container">
        <h2 class="text-center display-6 fw-bold mb-5">Our Core Values</h2>
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-heart-fill fs-3"></i>
                        </div>
                        <h3 class="card-title h5 fw-bold">Customer First</h3>
                        <p class="card-text">We prioritize our customers' needs and strive to exceed their expectations with quality service.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-tree-fill fs-3"></i>
                        </div>
                        <h3 class="card-title h5 fw-bold">Environmental Responsibility</h3>
                        <p class="card-text">We are committed to sustainable practices and reducing our environmental footprint.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                        <h3 class="card-title h5 fw-bold">Community Engagement</h3>
                        <p class="card-text">We actively participate in and support our local communities through various initiatives.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="mb-5">
    <h2 class="display-6 fw-bold text-center mb-5">Meet Our Team</h2>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php foreach ($teamMembers as $member): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="image-container" style="height: 250px; overflow: hidden;">
                        <img src="<?= htmlspecialchars($member['image']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($member['name']) ?>" 
                             style="width: 100%; height: 100%; object-fit: cover; object-position: center 20%;"
                             onerror="this.src='images/team/placeholder.jpg'">
                    </div>
                    <div class="card-body">
                        <h3 class="card-title h5 fw-bold"><?= htmlspecialchars($member['name']) ?></h3>
                        <p class="card-subtitle text-primary mb-2"><?= htmlspecialchars($member['position']) ?></p>
                        <p class="card-text small"><?= htmlspecialchars($member['bio']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Our Journey Timeline -->
<section class="mb-5 py-5 bg-light rounded">
    <div class="container">
        <h2 class="display-6 fw-bold text-center mb-5">Our Journey</h2>
        
        <div class="timeline position-relative">
            <?php foreach ($milestones as $index => $milestone): ?>
                <div class="timeline-item <?= $index % 2 == 0 ? 'left' : 'right' ?> position-relative mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <span class="badge bg-primary mb-2"><?= htmlspecialchars($milestone['year']) ?></span>
                            <h3 class="h5 fw-bold"><?= htmlspecialchars($milestone['title']) ?></h3>
                            <p class="mb-0"><?= htmlspecialchars($milestone['description']) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="mb-5">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="h3 fw-bold mb-4">Get In Touch</h2>
                    <p class="mb-4">We'd love to hear from you! If you have any questions, feedback, or inquiries, please don't hesitate to contact us.</p>
                    
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="bi bi-geo-alt-fill text-primary fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold">Address</h5>
                            <p>Keele University, Keele, Newcastle-under-Lyme ST5 5BG, United Kingdom</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="bi bi-telephone-fill text-primary fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold">Phone</h5>
                            <p>+44 1782 732000</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-3">
                        <div class="flex-shrink-0">
                            <i class="bi bi-envelope-fill text-primary fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold">Email</h5>
                            <p>info@keelegrocery.com</p>
                        </div>
                    </div>
                    
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock-fill text-primary fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="fw-bold">Store Hours</h5>
                            <p>Monday - Friday: 8AM - 9PM<br>Saturday - Sunday: 9AM - 7PM</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="map rounded overflow-hidden">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2398.935343665298!2d-2.2749848231277375!3d53.003433172770104!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x487a42334014a417%3A0x61216ac41f29e4b1!2sKeele%20University!5e0!3m2!1sen!2suk!4v1684349869317!5m2!1sen!2suk" width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Timeline styling */
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    width: 4px;
    background-color: var(--bs-primary);
    top: 0;
    bottom: 0;
    left: 50%;
    margin-left: -2px;
}

.timeline-item {
    padding: 10px 40px;
    position: relative;
    width: 50%;
}

.timeline-item::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    background-color: white;
    border: 4px solid var(--bs-primary);
    top: 15px;
    border-radius: 50%;
    z-index: 1;
}

.timeline-item.left {
    left: 0;
}

.timeline-item.right {
    left: 50%;
}

.timeline-item.left::after {
    right: -10px;
}

.timeline-item.right::after {
    left: -10px;
}

@media (max-width: 768px) {
    .timeline::before {
        left: 31px;
    }
    
    .timeline-item {
        width: 100%;
        padding-left: 70px;
        padding-right: 25px;
    }
    
    .timeline-item.right {
        left: 0;
    }
    
    .timeline-item.left::after,
    .timeline-item.right::after {
        left: 21px;
    }
}

/* Team member image styling */
.image-container {
    border-top-left-radius: .25rem;
    border-top-right-radius: .25rem;
    position: relative;
}

.image-container img {
    transition: transform 0.3s ease;
}

.card:hover .image-container img {
    transform: scale(1.05);
}

/* Ensure proper display on mobile devices */
@media (max-width: 576px) {
    .image-container {
        height: 300px !important;
    }
}
</style> 