<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mt-4">Edit Haircut History</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/history">History</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
        <div>
            <a href="/history/view/<?= $history['history_id'] ?>" class="btn btn-info">
                <i class="fas fa-eye"></i> View Details
            </a>
            <a href="/history" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to History
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> Edit Haircut Record
                    </h5>
                </div>
                <div class="card-body">
                    <form action="/history/update/<?= $history['history_id'] ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <!-- Style Information -->
                        <div class="mb-3">
                            <label for="style_name" class="form-label">Style Name *</label>
                            <input type="text" class="form-control <?= session()->getFlashdata('errors.style_name') ? 'is-invalid' : '' ?>"
                                id="style_name" name="style_name"
                                value="<?= old('style_name', $history['style_name']) ?>"
                                placeholder="e.g., Fade, Undercut, Pompadour" required>
                            <?php if (session()->getFlashdata('errors.style_name')): ?>
                                <div class="invalid-feedback">
                                    <?= session()->getFlashdata('errors.style_name') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Style Notes -->
                        <div class="mb-3">
                            <label for="style_notes" class="form-label">Style Notes</label>
                            <textarea class="form-control <?= session()->getFlashdata('errors.style_notes') ? 'is-invalid' : '' ?>"
                                id="style_notes" name="style_notes" rows="3"
                                placeholder="Describe the style, techniques used, or any special instructions..."><?= old('style_notes', $history['style_notes']) ?></textarea>
                            <?php if (session()->getFlashdata('errors.style_notes')): ?>
                                <div class="invalid-feedback">
                                    <?= session()->getFlashdata('errors.style_notes') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Services Used -->
                        <div class="mb-3">
                            <label class="form-label">Services Used</label>
                            <div class="row">
                                <?php
                                $selectedServices = [];
                                if (!empty($history['services_used'])) {
                                    $selectedServices = json_decode($history['services_used'], true) ?: [];
                                }
                                ?>
                                <?php foreach ($services as $service): ?>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                name="services_used[]"
                                                value="<?= $service['service_id'] ?>"
                                                id="service_<?= $service['service_id'] ?>"
                                                <?= in_array($service['service_id'], old('services_used', $selectedServices)) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="service_<?= $service['service_id'] ?>">
                                                <strong><?= esc($service['service_name']) ?></strong>
                                                <br>
                                                <small class="text-muted">₱<?= number_format($service['price'], 2) ?></small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Current 4-Panel Photos -->
                        <?php if ($history['top_photo'] || $history['left_side_photo'] || $history['right_side_photo'] || $history['back_photo']): ?>
                            <div class="mb-3">
                                <label class="form-label">Current Photos</label>
                                <div class="row">
                                    <?php if ($history['top_photo']): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title">Top View</h6>
                                                </div>
                                                <div class="card-body text-center">
                                                    <img src="<?= base_url('file/writable?path=' . $history['top_photo']) ?>"
                                                        class="img-fluid rounded"
                                                        alt="Top view"
                                                        style="max-height: 150px; object-fit: cover;">
                                                    <div class="mt-2">
                                                        <small class="text-muted">Current top photo</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($history['left_side_photo']): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title">Left Side</h6>
                                                </div>
                                                <div class="card-body text-center">
                                                    <img src="/<?= $history['left_side_photo'] ?>"
                                                        class="img-fluid rounded"
                                                        alt="Left side"
                                                        style="max-height: 150px; object-fit: cover;">
                                                    <div class="mt-2">
                                                        <small class="text-muted">Current left side photo</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($history['right_side_photo']): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title">Right Side</h6>
                                                </div>
                                                <div class="card-body text-center">
                                                    <img src="/<?= $history['right_side_photo'] ?>"
                                                        class="img-fluid rounded"
                                                        alt="Right side"
                                                        style="max-height: 150px; object-fit: cover;">
                                                    <div class="mt-2">
                                                        <small class="text-muted">Current right side photo</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($history['back_photo']): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h6 class="card-title">Back View</h6>
                                                </div>
                                                <div class="card-body text-center">
                                                    <img src="/<?= $history['back_photo'] ?>"
                                                        class="img-fluid rounded"
                                                        alt="Back view"
                                                        style="max-height: 150px; object-fit: cover;">
                                                    <div class="mt-2">
                                                        <small class="text-muted">Current back photo</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- New 4-Panel Photos -->
                        <div class="mb-3">
                            <label class="form-label">Update Photos (4-Panel System)</label>
                            <div class="row">
                                <!-- Top Panel -->
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="fas fa-arrow-up"></i> Top View
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <input type="file" class="form-control mb-2"
                                                name="top_photo" accept="image/*">
                                            <div class="form-text">Leave empty to keep current photo</div>
                                            <textarea class="form-control mt-2"
                                                name="top_description"
                                                rows="2"
                                                placeholder="Describe how you cut the top..."><?= old('top_description', $history['top_description']) ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Left Side Panel -->
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="fas fa-arrow-left"></i> Left Side
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <input type="file" class="form-control mb-2"
                                                name="left_side_photo" accept="image/*">
                                            <div class="form-text">Leave empty to keep current photo</div>
                                            <textarea class="form-control mt-2"
                                                name="left_side_description"
                                                rows="2"
                                                placeholder="Describe how you cut the left side..."><?= old('left_side_description', $history['left_side_description']) ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Side Panel -->
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="fas fa-arrow-right"></i> Right Side
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <input type="file" class="form-control mb-2"
                                                name="right_side_photo" accept="image/*">
                                            <div class="form-text">Leave empty to keep current photo</div>
                                            <textarea class="form-control mt-2"
                                                name="right_side_description"
                                                rows="2"
                                                placeholder="Describe how you cut the right side..."><?= old('right_side_description', $history['right_side_description']) ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Back Panel -->
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">
                                                <i class="fas fa-arrow-down"></i> Back View
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <input type="file" class="form-control mb-2"
                                                name="back_photo" accept="image/*">
                                            <div class="form-text">Leave empty to keep current photo</div>
                                            <textarea class="form-control mt-2"
                                                name="back_description"
                                                rows="2"
                                                placeholder="Describe how you cut the back..."><?= old('back_description', $history['back_description']) ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="/history/view/<?= $history['history_id'] ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Haircut History
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Record Information
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Record ID:</strong> #<?= $history['history_id'] ?>
                        </li>
                        <li class="mb-2">
                            <strong>Created:</strong> <?= date('M d, Y h:i A', strtotime($history['created_at'])) ?>
                        </li>
                        <li class="mb-2">
                            <strong>Last Updated:</strong> <?= date('M d, Y h:i A', strtotime($history['updated_at'])) ?>
                        </li>
                        <li class="mb-2">
                            <strong>Haircut Date:</strong> <?= date('M d, Y', strtotime($history['haircut_date'])) ?>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb"></i> Editing Tips
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            You can update any field except the appointment association
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Photos can be updated by uploading new files
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Services can be added or removed as needed
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            All changes are tracked with timestamps
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-tools"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/history/view/<?= $history['history_id'] ?>" class="btn btn-info">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <button type="button" class="btn btn-danger" onclick="deleteHistory(<?= $history['history_id'] ?>)">
                            <i class="fas fa-trash"></i> Delete Record
                        </button>
                    </div>
                </div>
            </div>
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

    // Preview images before upload
    $('#before_photo, #after_photo').change(function() {
        const file = this.files[0];
        const reader = new FileReader();
        const fieldName = $(this).attr('name');

        reader.onload = function(e) {
            // You can add image preview functionality here
            console.log('New image selected:', fieldName);
        };

        if (file) {
            reader.readAsDataURL(file);
        }
    });

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