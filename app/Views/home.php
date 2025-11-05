<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <?php if (!session()->get('user_id')): ?>
        <!-- Welcome Section for Non-Logged Users -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center mb-5">
                    <h1 class="display-4 text-primary mb-3">
                        <i class="fas fa-cut"></i> CleanCut
                    </h1>
                    <p class="lead text-muted">Professional Haircut Management System</p>
                    <p class="text-muted">Streamline your barbershop operations with our comprehensive booking and management platform</p>
                </div>

                <!-- Features Grid -->
                <div class="row mb-5">
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-calendar-check fa-2x text-primary"></i>
                                </div>
                                <h5 class="card-title">Easy Booking</h5>
                                <p class="card-text text-muted">Book appointments with your favorite barbers instantly</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-chart-line fa-2x text-success"></i>
                                </div>
                                <h5 class="card-title">Analytics Dashboard</h5>
                                <p class="card-text text-muted">Track earnings and performance with detailed analytics</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-comments fa-2x text-info"></i>
                                </div>
                                <h5 class="card-title">Real-time Chat</h5>
                                <p class="card-text text-muted">Communicate directly with barbers and customers</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-camera fa-2x text-warning"></i>
                                </div>
                                <h5 class="card-title">Work Showcase</h5>
                                <p class="card-text text-muted">Barbers can showcase their best work</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="text-center">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="card border-0 shadow">
                                <div class="card-body p-4">
                                    <h4 class="mb-3">Get Started Today</h4>
                                    <p class="text-muted mb-4">Join thousands of barbers and customers using CleanCut</p>
                                    <div class="d-grid gap-2 d-md-block">
                                        <a href="<?= base_url('register') ?>" class="btn btn-primary btn-lg me-md-2">
                                            <i class="fas fa-user-plus"></i> Create Account
                                        </a>
                                        <a href="<?= base_url('login') ?>" class="btn btn-outline-primary btn-lg">
                                            <i class="fas fa-sign-in-alt"></i> Sign In
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Dashboard for Logged Users -->
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info border-0">
                    <i class="fas fa-user-circle"></i>
                    Welcome back, <strong><?= session()->get('user_name') ?? 'User' ?></strong>!
                    You are logged in as a <strong><?= ucfirst(session()->get('role')) ?></strong>.
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0 text-primary">0</h3>
                                <p class="text-muted mb-0">Appointments</p>
                            </div>
                            <div class="text-primary">
                                <i class="fas fa-calendar-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0 text-success">0</h3>
                                <p class="text-muted mb-0">Messages</p>
                            </div>
                            <div class="text-success">
                                <i class="fas fa-comments fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0 text-info">0</h3>
                                <p class="text-muted mb-0">Services</p>
                            </div>
                            <div class="text-info">
                                <i class="fas fa-list fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0 text-warning">0</h3>
                                <p class="text-muted mb-0">Users</p>
                            </div>
                            <div class="text-warning">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if (session()->get('role') === 'customer'): ?>
                                <div class="col-md-4 mb-3">
                                    <a href="<?= base_url('booking') ?>" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-calendar-plus"></i> Book Appointment
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="<?= base_url('history') ?>" class="btn btn-info btn-lg w-100">
                                        <i class="fas fa-history"></i> View History
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="chat" class="btn btn-success btn-lg w-100">
                                        <i class="fas fa-comments"></i> Messages
                                    </a>
                                </div>
                            <?php elseif (session()->get('role') === 'barber'): ?>
                                <div class="col-md-4 mb-3">
                                    <a href="<?= base_url('analytics') ?>" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-chart-line"></i> Analytics
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="<?= base_url('social-feed') ?>" class="btn btn-info btn-lg w-100">
                                        <i class="fas fa-camera"></i> Work Showcase
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="chat" class="btn btn-success btn-lg w-100">
                                        <i class="fas fa-comments"></i> Messages
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="col-md-4 mb-3">
                                    <a href="<?= base_url('users') ?>" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-users"></i> User Management
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="<?= base_url('services') ?>" class="btn btn-info btn-lg w-100">
                                        <i class="fas fa-list"></i> Service Management
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="<?= base_url('analytics') ?>" class="btn btn-success btn-lg w-100">
                                        <i class="fas fa-chart-bar"></i> Analytics
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>