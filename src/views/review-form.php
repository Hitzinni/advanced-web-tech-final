<div class="container my-5">
    <!-- Page Header -->
    <div class="category-header position-relative mb-4 bg-primary text-white p-4 rounded shadow">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="fw-bold mb-2"><i class="bi bi-star me-2"></i>Write a Review</h1>
                <p class="lead mb-0">Share your experience with our grocery store</p>
            </div>
            <div class="col-auto">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-end mb-0">
                        <li class="breadcrumb-item"><a href="/" class="text-white">Home</a></li>
                        <li class="breadcrumb-item active text-white-50" aria-current="page">Reviews</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light py-3">
                    <h2 class="h5 mb-0"><i class="bi bi-pencil-square me-2"></i>Your Review</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['errors']) && is_array($_SESSION['errors'])): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($_SESSION['errors'] as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php unset($_SESSION['errors']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <?php unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="/review-submit" method="post" id="review-form">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Your Rating</label>
                            <div class="mb-3">
                                <div class="btn-group" role="group" aria-label="Star Rating">
                                    <?php 
                                    $currentRating = isset($_SESSION['form_data']['rating']) ? (int)$_SESSION['form_data']['rating'] : 0;
                                    for ($i = 1; $i <= 5; $i++): 
                                    ?>
                                        <input type="radio" class="btn-check" name="rating" id="star<?= $i ?>" value="<?= $i ?>" 
                                               <?= $currentRating === $i ? 'checked' : '' ?> required>
                                        <label class="btn btn-outline-warning" for="star<?= $i ?>">
                                            <i class="bi bi-star-fill"></i> <?= $i ?>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                                <div class="form-text mt-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Please select a rating from 1 to 5 stars
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="content" class="form-label fw-bold">Your Experience</label>
                            <textarea class="form-control" id="content" name="content" rows="5" required
                                placeholder="Share your thoughts about our products and services..."><?= htmlspecialchars(isset($_SESSION['form_data']['content']) ? $_SESSION['form_data']['content'] : '') ?></textarea>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Please share your honest feedback. Min 10 characters, max 1000 characters.
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <i class="bi bi-send me-2"></i>Submit Review
                            </button>
                            <a href="/" class="btn btn-outline-secondary px-4 py-2">
                                <i class="bi bi-arrow-left me-2"></i>Back to Home
                            </a>
                        </div>
                    </form>
                    
                    <?php
                    // Clear form data after displaying it
                    if (isset($_SESSION['form_data'])) {
                        unset($_SESSION['form_data']);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($userReviews)): ?>
    <div class="row mt-5">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light py-3">
                    <h2 class="h5 mb-0"><i class="bi bi-clock-history me-2"></i>Your Previous Reviews</h2>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($userReviews as $review): ?>
                            <div class="list-group-item p-4 hover-bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="text-warning me-3">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?= $i <= $review['rating'] ? '-fill' : '' ?> me-1"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="text-muted small">
                                            <i class="bi bi-calendar-event me-1"></i>
                                            <?= date('M j, Y', strtotime($review['created_at'])) ?>
                                        </span>
                                    </div>
                                    <form action="/review-delete" method="post" onsubmit="return confirm('Are you sure you want to delete this review?')">
                                        <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                                <div class="review-content p-3 bg-light rounded">
                                    <p class="mb-0"><?= htmlspecialchars($review['content']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    /* Review styling */
    .hover-bg-light:hover {
        background-color: rgba(0,0,0,0.02);
    }
    
    .review-content {
        border-left: 4px solid #198754;
    }
    
    .category-header {
        background: linear-gradient(135deg, #2c3e50, #3498db);
    }
    
    /* Ensure Bootstrap's btn-check works properly */
    .btn-check:checked + .btn-outline-warning {
        background-color: #ffc107;
        color: #000;
    }
</style> 