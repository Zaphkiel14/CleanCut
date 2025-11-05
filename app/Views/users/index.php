<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-users"></i> User Management</h3>
            <?php if (in_array($user_role, ['admin', 'owner'])): ?>
                <a href="<?= base_url('users/create') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New User
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <?php if (!empty($users)): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <?php if (in_array($user_role, ['admin', 'owner'])): ?>
                                    <th class="text-end">Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['user_id'] ?></td>
                                    <td>
                                        <strong><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                    </td>
                                    <td><?= esc($user['email']) ?></td>
                                    <td><?= esc($user['phone'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge bg-<?= getRoleBadgeColor($user['role']) ?>">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $user['is_active'] ? 'success' : 'danger' ?>">
                                            <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                    <?php if (in_array($user_role, ['admin', 'owner'])): ?>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= base_url('users/edit/' . $user['user_id']) ?>" 
                                                   class="btn btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <button class="btn btn-outline-secondary" 
                                                        onclick="toggleStatus(<?= $user['user_id'] ?>)"
                                                        title="Toggle Status">
                                                    <i class="fas fa-<?= $user['is_active'] ? 'ban' : 'check' ?>"></i>
                                                </button>
                                                
                                                <?php if ($user_role === 'admin' && $user['user_id'] != session()->get('user_id')): ?>
                                                    <form action="<?= base_url('users/delete/' . $user['user_id']) ?>" 
                                                          method="post" class="d-inline" 
                                                          onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-users fa-3x mb-3"></i>
                    <p>No users found.</p>
                    <?php if (in_array($user_role, ['admin', 'owner'])): ?>
                        <a href="<?= base_url('users/create') ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First User
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Total Users</h6>
                            <h3><?= count($users) ?></h3>
                        </div>
                        <div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Active Users</h6>
                            <h3><?= count(array_filter($users, fn($u) => $u['is_active'])) ?></h3>
                        </div>
                        <div>
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Barbers</h6>
                            <h3><?= count(array_filter($users, fn($u) => $u['role'] === 'barber')) ?></h3>
                        </div>
                        <div>
                            <i class="fas fa-cut fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>Customers</h6>
                            <h3><?= count(array_filter($users, fn($u) => $u['role'] === 'customer')) ?></h3>
                        </div>
                        <div>
                            <i class="fas fa-user fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function toggleStatus(userId) {
    if (confirm('Are you sure you want to toggle this user\'s status?')) {
        fetch(`<?= base_url('users/toggle-status') ?>/${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to update user status'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating user status');
        });
    }
}
</script>
<?= $this->endSection() ?>

<?php
// Helper function for role badge colors
function getRoleBadgeColor($role) {
    switch ($role) {
        case 'admin': return 'danger';
        case 'owner': return 'warning';
        case 'barber': return 'info';
        case 'customer': return 'secondary';
        default: return 'light';
    }
}
?>
