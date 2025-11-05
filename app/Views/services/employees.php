<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-user-tie"></i> Manage Barbers</h3>
            <?php if ($shop): ?>
                <span class="badge bg-primary">Shop: <?= esc($shop['shop_name']) ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php if (!empty($available_barbers)): ?>
    <div class="card mb-4">
        <div class="card-header"><strong>Assign Existing Barber</strong></div>
        <div class="card-body">
            <p class="text-muted mb-3">Select from existing barbers who have accounts on the website:</p>
            <form method="post" action="<?= base_url('services/employees/assign') ?>">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Select Barber</label>
                        <select class="form-control" name="barber_id" required>
                            <option value="">Choose a barber...</option>
                            <?php foreach ($available_barbers as $barber): ?>
                                <option value="<?= $barber['user_id'] ?>">
                                    <?= esc($barber['first_name'] . ' ' . $barber['last_name']) ?> (<?= esc($barber['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2"><i class="fas fa-user-plus"></i> Assign Barber</button>
                        <a href="<?= base_url('services') ?>" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="card mb-4">
        <div class="card-body text-center text-muted py-4">
            <i class="fas fa-info-circle fa-2x mb-2"></i>
            <p>No available barbers to assign. All existing barbers are already assigned to your shop.</p>
            <small>Barbers need to register themselves on the website with the "Barber" role first.</small>
            <br><a href="<?= base_url('services') ?>" class="btn btn-secondary mt-2">Back</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><strong>Current Barbers</strong></div>
        <div class="card-body">
            <?php if (!empty($employees)): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Name</th><th>Email</th><th>Position</th><th>Hire Date</th></tr></thead>
                        <tbody>
                        <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td><?= esc(($emp['first_name'] ?? '').' '.($emp['last_name'] ?? '')) ?></td>
                                <td><?= esc($emp['email'] ?? '') ?></td>
                                <td><?= esc($emp['position'] ?? 'Barber') ?></td>
                                <td><?= esc($emp['hire_date'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-user-times fa-2x mb-2"></i>
                    <p>No barbers yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

