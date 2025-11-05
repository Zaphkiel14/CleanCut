<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-list"></i> Service Management</h3>
            <div class="d-flex align-items-center gap-2">
                <?php if ($user_role === 'owner' && $shop): ?>
                    <span class="badge bg-primary">Shop: <?= esc($shop['shop_name']) ?></span>
                    <a href="<?= base_url('services/create') ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add Service
                    </a>
                    <a href="<?= base_url('services/employees') ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-user-tie"></i> Manage Barbers
                    </a>
                <?php elseif ($user_role === 'admin'): ?>
                    <a href="<?= base_url('services/create') ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Add Service
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (!empty($services)): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <?php if ($user_role === 'admin'): ?>
                                    <th>Shop ID</th>
                                <?php endif; ?>
                                <th>Service</th>
                                <th>Price</th>
                                <?php if (in_array($user_role, ['owner','admin'])): ?>
                                    <th class="text-end">Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $svc): ?>
                                <tr>
                                    <?php if ($user_role === 'admin'): ?>
                                        <td><?= esc($svc['shop_id']) ?></td>
                                    <?php endif; ?>
                                    <td><?= esc($svc['service_name']) ?></td>
                                    <td>₱<?= number_format((float)($svc['price'] ?? 0), 2) ?></td>
                                    <?php if (in_array($user_role, ['owner','admin'])): ?>
                                        <td class="text-end">
                                            <a href="<?= base_url('services/edit/' . $svc['service_id']) ?>" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?= base_url('services/delete/' . $svc['service_id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this service?');">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                    <p>No services found.</p>
                    <?php if (in_array($user_role, ['owner', 'admin'])): ?>
                        <a href="<?= base_url('services/create') ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Service
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>