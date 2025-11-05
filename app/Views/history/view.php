<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mt-4">Haircut Details</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/history">History</a></li>
                <li class="breadcrumb-item active">Details</li>
            </ol>
        </div>
        <div>
            <?php if (session()->get('role') === 'barber'): ?>
                <a href="/history/edit/<?= $history['history_id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
            <?php endif; ?>
            <a href="/history" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to History
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cut"></i> 
                        <?= esc($history['style_name']) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Date & Time</h6>
                            <p class="mb-3">
                                <i class="fas fa-calendar"></i>
                                <?= date('F d, Y', strtotime($history['haircut_date'])) ?>
                                <br>
                                <small class="text-muted">
                                    <?= date('h:i A', strtotime($history['created_at'])) ?>
                                </small>
                            </p>

                            <h6 class="text-muted">Style Notes</h6>
                            <p class="mb-3">
                                <?= $history['style_notes'] ? esc($history['style_notes']) : '<em class="text-muted">No notes provided</em>' ?>
                            </p>

                            <h6 class="text-muted">Total Cost</h6>
                            <p class="mb-3">
                                <span class="badge bg-success fs-6">₱<?= number_format($history['total_cost'], 2) ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Customer Rating</h6>
                            <div class="mb-3">
                                <?php if ($history['customer_rating']): ?>
                                    <div class="d-flex align-items-center">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= $history['customer_rating'] ? 'text-warning' : 'text-muted' ?> fs-5"></i>
                                        <?php endfor; ?>
                                        <span class="ms-2 fs-6"><?= $history['customer_rating'] ?>/5</span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No rating provided</span>
                                <?php endif; ?>
                            </div>

                            <h6 class="text-muted">Customer Feedback</h6>
                            <p class="mb-3">
                                <?= $history['customer_feedback'] ? esc($history['customer_feedback']) : '<em class="text-muted">No feedback provided</em>' ?>
                            </p>
                            
                            <?php if (session()->get('role') === 'customer' && session()->get('user_id') == $history['customer_id']): ?>
                                <?php if (!$history['customer_rating']): ?>
                                    <div class="mt-3">
                                        <a href="<?= base_url('history/rate/' . $history['history_id']) ?>" class="btn btn-warning btn-sm">
                                            <i class="fas fa-star"></i> Rate This Haircut
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-3">
                                        <a href="<?= base_url('history/rate/' . $history['history_id']) ?>" class="btn btn-outline-warning btn-sm">
                                            <i class="fas fa-edit"></i> Update Rating
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services Used -->
            <?php if (!empty($services)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Services Used
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($services as $service): ?>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                                    <div>
                                        <strong><?= esc($service['service_name']) ?></strong>
                                        <?php if ($service['description']): ?>
                                            <br><small class="text-muted"><?= esc($service['description']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <span class="badge bg-primary">₱<?= number_format($service['price'], 2) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- 4-Panel Photos -->
            <?php if ($history['top_photo'] || $history['left_side_photo'] || $history['right_side_photo'] || $history['back_photo']): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-images"></i> Haircut Photos (4-Panel System)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Top Panel -->
                        <?php if ($history['top_photo']): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-arrow-up"></i> Top View
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <img src="<?= base_url('file/writable?path=' . $history['top_photo']) ?>" 
                                             class="img-fluid rounded mb-2" 
                                             alt="Top view"
                                             style="max-height: 200px; object-fit: cover;">
                                        <?php if ($history['top_description']): ?>
                                            <p class="mb-0"><strong>Description:</strong> <?= esc($history['top_description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Left Side Panel -->
                        <?php if ($history['left_side_photo']): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-arrow-left"></i> Left Side
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <img src="<?= base_url('file/writable?path=' . $history['left_side_photo']) ?>" 
                                             class="img-fluid rounded mb-2" 
                                             alt="Left side"
                                             style="max-height: 200px; object-fit: cover;">
                                        <?php if ($history['left_side_description']): ?>
                                            <p class="mb-0"><strong>Description:</strong> <?= esc($history['left_side_description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Right Side Panel -->
                        <?php if ($history['right_side_photo']): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-arrow-right"></i> Right Side
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <img src="<?= base_url('file/writable?path=' . $history['right_side_photo']) ?>" 
                                             class="img-fluid rounded mb-2" 
                                             alt="Right side"
                                             style="max-height: 200px; object-fit: cover;">
                                        <?php if ($history['right_side_description']): ?>
                                            <p class="mb-0"><strong>Description:</strong> <?= esc($history['right_side_description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Back Panel -->
                        <?php if ($history['back_photo']): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">
                                            <i class="fas fa-arrow-down"></i> Back View
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <img src="<?= base_url('file/writable?path=' . $history['back_photo']) ?>" 
                                             class="img-fluid rounded mb-2" 
                                             alt="Back view"
                                             style="max-height: 200px; object-fit: cover;">
                                        <?php if ($history['back_description']): ?>
                                            <p class="mb-0"><strong>Description:</strong> <?= esc($history['back_description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Customer/Barber Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user"></i> 
                        <?= session()->get('role') === 'barber' ? 'Customer Information' : 'Barber Information' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (session()->get('role') === 'barber'): ?>
                        <div class="text-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x"></i>
                            </div>
                        </div>
                        <h6 class="text-center"><?= esc($customer['first_name'] . ' ' . $customer['last_name']) ?></h6>
                        <p class="text-center text-muted mb-0"><?= esc($customer['email']) ?></p>
                        <?php if ($customer['phone']): ?>
                            <p class="text-center text-muted"><?= esc($customer['phone']) ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center mb-3">
                            <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-user-tie fa-2x"></i>
                            </div>
                        </div>
                        <h6 class="text-center"><?= esc($barber['first_name'] . ' ' . $barber['last_name']) ?></h6>
                        <p class="text-center text-muted mb-0"><?= esc($barber['email']) ?></p>
                        <?php if ($barber['phone']): ?>
                            <p class="text-center text-muted"><?= esc($barber['phone']) ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Appointment Details -->
            <?php if ($appointment): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check"></i> Appointment Details
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Status:</strong>
                        <span class="badge bg-<?= $appointment['status'] === 'completed' ? 'success' : 'secondary' ?>">
                            <?= ucfirst($appointment['status']) ?>
                        </span>
                    </p>
                    <p class="mb-2">
                        <strong>Date:</strong><br>
                        <?= date('F d, Y', strtotime($appointment['appointment_date'])) ?>
                    </p>
                    <p class="mb-0">
                        <strong>Time:</strong><br>
                        <?= date('h:i A', strtotime($appointment['appointment_time'])) ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <?php if (session()->get('role') === 'barber'): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tools"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/history/edit/<?= $history['history_id'] ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Details
                        </a>
                        <button type="button" class="btn btn-danger" onclick="deleteHistory(<?= $history['history_id'] ?>)">
                            <i class="fas fa-trash"></i> Delete Record
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this haircut history record? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function deleteHistory(historyId) {
    if (confirm('Are you sure you want to delete this haircut history record?')) {
        window.location.href = `/history/delete/${historyId}`;
    }
}

// Show success/error messages
<?php if (session()->getFlashdata('success')): ?>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= session()->getFlashdata('success') ?>',
        timer: 3000,
        showConfirmButton: false
    });
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: '<?= session()->getFlashdata('error') ?>'
    });
<?php endif; ?>
</script>
<?= $this->endSection() ?>
