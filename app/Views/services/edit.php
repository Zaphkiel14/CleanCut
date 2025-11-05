<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-edit"></i> Edit Service</h3>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="post" action="<?= base_url('services/update/' . $service['service_id']) ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">Service Name</label>
                    <input type="text" class="form-control" name="service_name" value="<?= esc($service['service_name']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description (optional)</label>
                    <textarea class="form-control" name="description" rows="3"><?= esc($service['description']) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" min="0" class="form-control" name="price" value="<?= esc($service['price']) ?>" required>
                        </div>
                    </div>
                    <!-- Optional duration, kept for backward compatibility -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Duration (optional)</label>
                        <div class="input-group">
                            <input type="number" min="5" step="5" class="form-control" name="duration" value="<?= esc($service['duration'] ?? '') ?>">
                            <span class="input-group-text">mins</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                    <a href="<?= base_url('services') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


