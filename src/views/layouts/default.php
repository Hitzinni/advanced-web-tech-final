<?php if (isset($_SESSION['user_id'])): ?>
    <a href="<?= \App\Helpers\View::url('my-orders') ?>" class="dropdown-item">
        <i class="bi bi-bag-check me-2"></i>My Orders
    </a>
<?php endif; ?> 