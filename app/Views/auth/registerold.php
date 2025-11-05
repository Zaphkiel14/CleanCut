<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-user-plus"></i> Create Account
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('register') ?>" method="post">
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    <i class="fas fa-user"></i> First Name *
                                </label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= old('first_name') ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">
                                    <i class="fas fa-user"></i> Last Name *
                                </label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= old('last_name') ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="middle_name" class="form-label">
                                    <i class="fas fa-user"></i> Middle Name
                                </label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name" 
                                       value="<?= old('middle_name') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email Address *
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= old('email') ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="birthdate" class="form-label">
                                    <i class="fas fa-calendar"></i> Birthdate
                                </label>
                                <input type="date" class="form-control" id="birthdate" name="birthdate" 
                                       value="<?= old('birthdate') ?>">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">
                                    <i class="fas fa-venus-mars"></i> Gender
                                </label>
                                <select class="form-select" id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male" <?= old('gender') == 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= old('gender') == 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= old('gender') == 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="contact_info" class="form-label">
                                <i class="fas fa-phone"></i> Contact Information
                            </label>
                            <input type="text" class="form-control" id="contact_info" name="contact_info" 
                                   value="<?= old('contact_info') ?>" placeholder="Phone number or other contact info">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i> Password *
                                </label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock"></i> Confirm Password *
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">
                                <i class="fas fa-user-tag"></i> Account Type *
                            </label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Account Type</option>
                                <option value="customer" <?= old('role') == 'customer' ? 'selected' : '' ?>>Customer</option>
                                <option value="barber" <?= old('role') == 'barber' ? 'selected' : '' ?>>Barber</option>
                                <option value="owner" <?= old('role') == 'owner' ? 'selected' : '' ?>>Shop Owner</option>
                                <option value="admin" <?= old('role') == 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                            <small class="text-muted">
                                <strong>Customer:</strong> Book appointments and manage your haircut history<br>
                                <strong>Barber:</strong> Manage appointments, showcase work, and track earnings<br>
                                <strong>Shop Owner:</strong> Manage your barbershop, barbers, and services<br>
                                <strong>Admin:</strong> System administration and user management
                            </small>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-primary">Terms of Service</a> and 
                                <a href="#" class="text-primary">Privacy Policy</a>
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-2">Already have an account?</p>
                        <a href="<?= base_url('login') ?>" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?> 