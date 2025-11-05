<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-plus"></i> Add Service</h3>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" action="<?= base_url('services/store') ?>">
                <?= csrf_field() ?>

                <?php if ($user_role === 'admin'): ?>
                    <div class="mb-3">
                        <label class="form-label">Shop ID</label>
                        <input type="number" class="form-control" name="shop_id" placeholder="Enter shop ID" required>
                        <small class="text-muted">Admin only: specify the shop this service belongs to.</small>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Service Name</label>
                    <input type="text" class="form-control" name="service_name" placeholder="e.g., Haircut, Beard Trim" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description (optional)</label>
                    <textarea class="form-control" name="description" rows="3" placeholder="Short description"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" min="0" class="form-control" name="price" required>
                        </div>
                    </div>
                    <!-- Duration removed; availability is controlled by barber schedules -->
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                    <a href="<?= base_url('services') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

