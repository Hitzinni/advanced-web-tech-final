<!-- Page Header Section -->
<div class="category-header position-relative mb-4 bg-primary text-white p-4 rounded shadow">
    <div class="row align-items-center">
        <div class="col-lg-6">
            <h1 class="fw-bold mb-2"><i class="bi bi-person-gear me-2"></i>User Management</h1>
            <p class="lead mb-0">Manage users and their roles</p>
        </div>
        <div class="col-lg-6 text-lg-end">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-lg-end mb-0">
                    <li class="breadcrumb-item"><a href="home" class="text-white">Home</a></li>
                    <li class="breadcrumb-item active text-white-50" aria-current="page">User Management</li>
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

<!-- Users Table -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-light py-3">
        <h2 class="h5 mb-0"><i class="bi bi-people me-2"></i>All Users</h2>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="badge <?= $user['role'] === 'manager' ? 'bg-danger' : 'bg-primary' ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <span class="text-muted"><i class="bi bi-info-circle me-1"></i>Current User</span>
                                <?php else: ?>
                                    <?php if ($user['role'] === 'customer'): ?>
                                        <a href="update-user-role?id=<?= $user['id'] ?>&role=manager" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-person-fill-gear me-1"></i>Make Admin
                                        </a>
                                    <?php else: ?>
                                        <a href="update-user-role?id=<?= $user['id'] ?>&role=customer" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-person me-1"></i>Remove Admin
                                        </a>
                                    <?php endif; ?>
                                    <a href="reset-user-password?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-warning ms-1" onclick="return confirm('Are you sure you want to reset this user\'s password? They will be assigned a temporary password.')">
                                        <i class="bi bi-key me-1"></i>Reset Password
                                    </a>
                                    <a href="delete-user?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                        <i class="bi bi-trash me-1"></i>Delete
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Admin Instructions -->
<div class="card bg-light mb-4">
    <div class="card-body">
        <h3 class="h5 mb-3"><i class="bi bi-info-circle me-2"></i>Admin Role Information</h3>
        <p>Users with the <strong>manager</strong> role have special privileges:</p>
        <ul>
            <li>Can update the status of any order</li>
            <li>Can manage user roles</li>
        </ul>
        <p class="mb-0 text-danger"><strong>Note:</strong> Be careful when assigning admin privileges. Admins can change the role of other users.</p>
    </div>
</div> 