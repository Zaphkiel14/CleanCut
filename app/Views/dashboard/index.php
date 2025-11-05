<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </h1>
                <div>
                    <span class="badge bg-primary fs-6">
                        <i class="fas fa-user-tag"></i> <?= ucfirst($user_role) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <?php if ($user_role === 'customer'): ?>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $total_appointments ?? 0 ?></div>
                            <div class="label">Total Appointments</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $completed_appointments ?? 0 ?></div>
                            <div class="label">Completed</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= count($appointments ?? []) ?></div>
                            <div class="label">Upcoming</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= count($history ?? []) ?></div>
                            <div class="label">History</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-history"></i>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($user_role === 'barber'): ?>
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= count($today_appointments ?? []) ?></div>
                            <div class="label">Today's Appointments</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $total_appointments ?? 0 ?></div>
                            <div class="label">Total Appointments</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $completed_appointments ?? 0 ?></div>
                            <div class="label">Completed</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= count($upcoming_appointments ?? []) ?></div>
                            <div class="label">Upcoming</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($user_role === 'owner'): ?>
            <!-- Shop Owner Dashboard -->
            <?php if (isset($shop) && $shop): ?>
                <div class="col-12 mb-4">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5><i class="fas fa-store"></i> <?= $shop['shop_name'] ?></h5>
                                <p class="mb-0"><?= $shop['address'] ?> | <?= $shop['phone'] ?></p>
                            </div>
                            <div>
                                <a href="#" class="btn btn-sm btn-outline-primary me-2">
                                    <i class="fas fa-edit"></i> Manage Shop
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $total_users ?? 0 ?></div>
                            <div class="label">Shop Customers</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $total_services ?? 0 ?></div>
                            <div class="label">Shop Services</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-list"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $total_appointments ?? 0 ?></div>
                            <div class="label">Shop Appointments</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= count($shop_employees ?? []) ?></div>
                            <div class="label">Shop Employees</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Admin Dashboard -->
            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $total_users ?? 0 ?></div>
                            <div class="label">Total Users</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $total_services ?? 0 ?></div>
                            <div class="label">Services</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-list"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $total_appointments ?? 0 ?></div>
                            <div class="label">Total Appointments</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="number"><?= $total_shops ?? 0 ?></div>
                            <div class="label">Total Shops</div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-store"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Content Sections -->
    <div class="row">
        <?php if ($user_role === 'customer'): ?>






        <?php elseif ($user_role === 'barber'): ?>
            <!-- Barber Dashboard -->
            <div class="col-lg-8">
                <!-- Barber dashboard content - clean and minimal -->
                </div>

        <?php elseif ($user_role === 'owner'): ?>
            <!-- Shop Owner Dashboard Content -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user-tie"></i> Shop Employees
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($shop_employees)): ?>
                            <?php foreach ($shop_employees as $employee): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                    <div>
                                        <strong><?= $employee['first_name'] . ' ' . $employee['last_name'] ?></strong><br>
                                        <small class="text-muted"><?= $employee['email'] ?></small>
                                    </div>
                                    <span class="badge bg-primary"><?= $employee['position'] ?? 'Employee' ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-user-tie fa-2x mb-2"></i>
                                <p>No employees yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Admin Dashboard -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-check"></i> Recent Appointments
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_appointments)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Barber</th>
                                            <th>Service</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_appointments as $appointment): ?>
                                            <tr>
                                                <td><?= date('M d, Y', strtotime($appointment['appointment_date'])) ?></td>
                                                <td><?= $appointment['customer_first_name'] . ' ' . $appointment['customer_last_name'] ?></td>
                                                <td><?= $appointment['barber_first_name'] . ' ' . $appointment['barber_last_name'] ?></td>
                                                <td><?= $appointment['service_name'] ?></td>
                                                <td>
                                                    <span class="status-badge status-<?= $appointment['status'] ?>">
                                                        <?= ucfirst($appointment['status']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                <p>No recent appointments</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-users"></i> Recent Users
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_users)): ?>
                            <?php foreach ($recent_users as $user): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                    <div>
                                        <strong><?= $user['first_name'] . ' ' . $user['last_name'] ?></strong><br>
                                        <small class="text-muted"><?= $user['email'] ?></small>
                                    </div>
                                    <span class="badge bg-secondary"><?= ucfirst($user['role']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <p>No recent users</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>