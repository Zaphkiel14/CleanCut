<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-4 col-lg-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-comments"></i> Messages</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <a href="<?= base_url('chat/conversation/' . $user['user_id']) ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0"><?= esc($user['first_name'] . ' ' . $user['last_name']) ?></h6>
                                            <small class="text-muted">Click to start chatting</small>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center text-muted">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <p>No users available to chat with</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="col-md-8 col-lg-9">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-comment-dots"></i> 
                        Select a conversation to start chatting
                    </h5>
                </div>
                <div class="card-body text-center py-5">
                    <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Welcome to Messages</h4>
                    <p class="text-muted">Choose a user from the sidebar to start a conversation</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 16px;
    font-weight: bold;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

.list-group-item.active {
    background-color: #007bff;
    border-color: #007bff;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}
</style>
<?= $this->endSection() ?>