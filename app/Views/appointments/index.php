<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0">
                <i class="fas fa-calendar-check"></i> <?= esc($title) ?>
            </h3>
            <?php if ($user_role === 'customer'): ?>
                <a href="<?= base_url('booking') ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Book New Appointment
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Status Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= $status_counts['pending'] ?></h4>
                            <p class="card-text">Pending</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= $status_counts['confirmed'] ?></h4>
                            <p class="card-text">Confirmed</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= $status_counts['completed'] ?></h4>
                            <p class="card-text">Completed</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-thumbs-up fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title"><?= $status_counts['cancelled'] ?></h4>
                            <p class="card-text">Cancelled</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Appointments</strong>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="filterByStatus('all')">All</button>
                <button type="button" class="btn btn-outline-warning btn-sm" onclick="filterByStatus('pending')">Pending</button>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="filterByStatus('confirmed')">Confirmed</button>
                <button type="button" class="btn btn-outline-success btn-sm" onclick="filterByStatus('completed')">Completed</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="filterByStatus('cancelled')">Cancelled</button>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($appointments)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-calendar-times fa-3x mb-3"></i>
                    <h5>No appointments found</h5>
                    <p>
                        <?php if ($user_role === 'customer'): ?>
                            You haven't booked any appointments yet.
                        <?php elseif ($user_role === 'barber'): ?>
                            No appointments assigned to you yet.
                        <?php else: ?>
                            No appointments in the system yet.
                        <?php endif; ?>
                    </p>
                    <?php if ($user_role === 'customer'): ?>
                        <a href="<?= base_url('booking') ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Book Your First Appointment
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="appointmentsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <?php if ($user_role !== 'customer'): ?>
                                    <th>Customer</th>
                                <?php endif; ?>
                                <?php if ($user_role !== 'barber'): ?>
                                    <th>Barber</th>
                                <?php endif; ?>
                                <?php if ($user_role === 'admin' || $user_role === 'owner'): ?>
                                    <th>Shop</th>
                                <?php endif; ?>
                                <th>Service</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr data-status="<?= $appointment['status'] ?>">
                                    <td>
                                        <strong><?= date('M j, Y', strtotime($appointment['appointment_date'])) ?></strong><br>
                                        <small class="text-muted"><?= date('g:i A', strtotime($appointment['appointment_time'])) ?></small>
                                    </td>
                                    <?php if ($user_role !== 'customer'): ?>
                                        <td>
                                            <?= esc($appointment['customer_first_name'] . ' ' . $appointment['customer_last_name']) ?><br>
                                            <small class="text-muted"><?= esc($appointment['customer_email']) ?></small>
                                        </td>
                                    <?php endif; ?>
                                    <?php if ($user_role !== 'barber'): ?>
                                        <td>
                                            <?= esc($appointment['barber_first_name'] . ' ' . $appointment['barber_last_name']) ?><br>
                                            <small class="text-muted"><?= esc($appointment['barber_email']) ?></small>
                                        </td>
                                    <?php endif; ?>
                                    <?php if ($user_role === 'admin' || $user_role === 'owner'): ?>
                                        <td><?= esc($appointment['shop_name'] ?? 'N/A') ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <strong><?= esc($appointment['service_name']) ?></strong><br>
                                        <?php if (isset($appointment['duration'])): ?>
                                            <small class="text-muted"><?= $appointment['duration'] ?> min</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong>₱<?= number_format($appointment['price'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'pending' => 'warning',
                                            'confirmed' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'secondary'
                                        ][$appointment['status']] ?? 'primary';
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?> status-badge">
                                            <?= ucfirst($appointment['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-info btn-sm" 
                                                    onclick="viewDetails(<?= $appointment['appointment_id'] ?>)" 
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <?php if ($appointment['status'] === 'pending'): ?>
                                                <?php if ($user_role === 'barber' || $user_role === 'owner' || $user_role === 'admin'): ?>
                                                    <button class="btn btn-outline-success btn-sm" 
                                                            onclick="updateStatus(<?= $appointment['appointment_id'] ?>, 'confirmed')" 
                                                            title="Confirm">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="updateStatus(<?= $appointment['appointment_id'] ?>, 'cancelled')" 
                                                        title="Cancel">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php elseif ($appointment['status'] === 'confirmed'): ?>
                                                <?php if ($user_role === 'barber' || $user_role === 'owner' || $user_role === 'admin'): ?>
                                                    <button class="btn btn-outline-success btn-sm" 
                                                            onclick="updateStatus(<?= $appointment['appointment_id'] ?>, 'completed')" 
                                                            title="Mark Complete">
                                                        <i class="fas fa-thumbs-up"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="updateStatus(<?= $appointment['appointment_id'] ?>, 'cancelled')" 
                                                        title="Cancel">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php elseif ($appointment['status'] === 'cancelled' && ($user_role === 'admin' || $user_role === 'owner')): ?>
                                                <button class="btn btn-outline-primary btn-sm" 
                                                        onclick="updateStatus(<?= $appointment['appointment_id'] ?>, 'pending')" 
                                                        title="Reschedule">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="appointmentDetails">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function filterByStatus(status) {
    const rows = document.querySelectorAll('#appointmentsTable tbody tr');
    const buttons = document.querySelectorAll('.btn-group button');
    
    // Update button states
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filter rows
    rows.forEach(row => {
        if (status === 'all' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function updateStatus(appointmentId, newStatus) {
    const confirmMessage = `Are you sure you want to ${newStatus} this appointment?`;
    if (!confirm(confirmMessage)) return;
    
    const formData = new FormData();
    formData.append('status', newStatus);
    // CSRF protection
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    fetch(`<?= base_url('appointments/update-status/') ?>${appointmentId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async response => {
        let data;
        try {
            data = await response.json();
        } catch (e) {
            throw new Error('Invalid server response');
        }
        return data;
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the appointment.');
    });
}

function viewDetails(appointmentId) {
    fetch(`<?= base_url('appointments/details/') ?>${appointmentId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const appointment = data.appointment;
            const detailsHtml = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Customer Information</h6>
                        <p><strong>Name:</strong> ${appointment.customer_first_name} ${appointment.customer_last_name}</p>
                        <p><strong>Email:</strong> ${appointment.customer_email}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Barber Information</h6>
                        <p><strong>Name:</strong> ${appointment.barber_first_name} ${appointment.barber_last_name}</p>
                        <p><strong>Email:</strong> ${appointment.barber_email}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Appointment Details</h6>
                        <p><strong>Date:</strong> ${new Date(appointment.appointment_date).toLocaleDateString()}</p>
                        <p><strong>Time:</strong> ${new Date('2000-01-01 ' + appointment.appointment_time).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>
                        <p><strong>Status:</strong> <span class="badge bg-primary">${appointment.status}</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Service Information</h6>
                        <p><strong>Service:</strong> ${appointment.service_name}</p>
                        <p><strong>Price:</strong> $${parseFloat(appointment.price).toFixed(2)}</p>
                        ${appointment.duration ? `<p><strong>Duration:</strong> ${appointment.duration} minutes</p>` : ''}
                    </div>
                </div>
                ${appointment.notes ? `<hr><h6>Notes</h6><p>${appointment.notes}</p>` : ''}
            `;
            
            document.getElementById('appointmentDetails').innerHTML = detailsHtml;
            new bootstrap.Modal(document.getElementById('appointmentModal')).show();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while loading appointment details.');
    });
}

// Auto-refresh appointments every 30 seconds for barbers and owners
<?php if ($user_role === 'barber' || $user_role === 'owner'): ?>
setInterval(() => {
    // Only refresh if no modal is open
    if (!document.querySelector('.modal.show')) {
        location.reload();
    }
}, 30000);
<?php endif; ?>
</script>

<style>
.status-badge {
    font-size: 0.85em;
    padding: 0.375rem 0.75rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    background-color: #f8f9fa;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.9em;
    }
    
    .btn-group-sm .btn {
        padding: 0.125rem 0.25rem;
        font-size: 0.75rem;
    }
}
</style>

<?= $this->endSection() ?>
