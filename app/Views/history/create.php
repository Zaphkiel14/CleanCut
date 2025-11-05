<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mt-4">Add Haircut History</h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/history">History</a></li>
                <li class="breadcrumb-item active">Add New</li>
            </ol>
        </div>
        <div>
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
                        <i class="fas fa-plus"></i> New Haircut Record
                    </h5>
                </div>
                <div class="card-body">
                    <form action="/history/store" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        
                        <!-- Appointment Selection -->
                        <div class="mb-3">
                            <label for="appointment_id" class="form-label">Select Appointment *</label>
                            <select class="form-select <?= session()->getFlashdata('errors.appointment_id') ? 'is-invalid' : '' ?>" 
                                    id="appointment_id" name="appointment_id" required>
                                <option value="">Choose an appointment...</option>
                                <?php foreach ($appointments as $appointment): ?>
                                    <option value="<?= $appointment['appointment_id'] ?>" 
                                            <?= old('appointment_id') == $appointment['appointment_id'] ? 'selected' : '' ?>>
                                        <?= date('M d, Y h:i A', strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time'])) ?> - 
                                        <?= esc($appointment['customer_first_name'] . ' ' . $appointment['customer_last_name']) ?> - 
                                        <?= esc($appointment['service_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session()->getFlashdata('errors.appointment_id')): ?>
                                <div class="invalid-feedback">
                                    <?= session()->getFlashdata('errors.appointment_id') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Style Information -->
                        <div class="mb-3">
                            <label for="style_name" class="form-label">Style Name *</label>
                            <input type="text" class="form-control <?= session()->getFlashdata('errors.style_name') ? 'is-invalid' : '' ?>" 
                                   id="style_name" name="style_name" 
                                   value="<?= old('style_name') ?>" 
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
                                      placeholder="Describe the style, techniques used, or any special instructions..."><?= old('style_notes') ?></textarea>
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
                                <?php foreach ($services as $service): ?>
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   name="services_used[]" 
                                                   value="<?= $service['service_id'] ?>" 
                                                   id="service_<?= $service['service_id'] ?>"
                                                   <?= in_array($service['service_id'], old('services_used') ?? []) ? 'checked' : '' ?>>
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

                        <!-- 4-Panel Photo System -->
                        <div class="mb-3">
                            <label class="form-label">Haircut Photos (4-Panel System)</label>
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
                                            <textarea class="form-control" 
                                                      name="top_description" 
                                                      rows="2" 
                                                      placeholder="Describe how you cut the top..."><?= old('top_description') ?></textarea>
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
                                            <textarea class="form-control" 
                                                      name="left_side_description" 
                                                      rows="2" 
                                                      placeholder="Describe how you cut the left side..."><?= old('left_side_description') ?></textarea>
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
                                            <textarea class="form-control" 
                                                      name="right_side_description" 
                                                      rows="2" 
                                                      placeholder="Describe how you cut the right side..."><?= old('right_side_description') ?></textarea>
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
                                            <textarea class="form-control" 
                                                      name="back_description" 
                                                      rows="2" 
                                                      placeholder="Describe how you cut the back..."><?= old('back_description') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="/history" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Haircut History
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
                        <i class="fas fa-info-circle"></i> Instructions
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Select the completed appointment from the dropdown
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Provide a descriptive style name for easy reference
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Add detailed notes about the style and techniques used
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Upload before and after photos for visual reference
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Select services used - total cost will be calculated automatically
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Record customer rating and feedback if available
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb"></i> Tips
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Style Names:</strong> Use consistent naming conventions (e.g., "High Fade", "Undercut", "Pompadour")
                        </li>
                        <li class="mb-2">
                            <strong>Photos:</strong> Good lighting and clear angles help with future reference
                        </li>
                        <li class="mb-2">
                            <strong>Notes:</strong> Include specific techniques, products used, or special requests
                        </li>
                        <li class="mb-2">
                            <strong>Services:</strong> Select the services used - the total cost will be automatically calculated based on service prices
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Preview images before upload
    $('#before_photo, #after_photo').change(function() {
        const file = this.files[0];
        const reader = new FileReader();
        const fieldName = $(this).attr('name');
        
        reader.onload = function(e) {
            // You can add image preview functionality here
            console.log('Image selected:', fieldName);
        };
        
        if (file) {
            reader.readAsDataURL(file);
        }
    });
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
