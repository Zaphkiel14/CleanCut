<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-percentage"></i> Commission Settings</h3>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- Shop Info -->
    <?php if (!empty($shop)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-store"></i> <?= esc($shop['shop_name']) ?>
                    </h5>
                    <p class="card-text mb-0"><?= esc($shop['address']) ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Admin Shop Selection -->
    <?php if ($user_role === 'admin' && !empty($shops)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="get">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label for="shop_id" class="form-label">Select Shop</label>
                                <select class="form-select" id="shop_id" name="shop_id" onchange="this.form.submit()">
                                    <?php foreach ($shops as $shopOption): ?>
                                        <option value="<?= $shopOption['shop_id'] ?>" 
                                                <?= ($shop['shop_id'] ?? '') == $shopOption['shop_id'] ? 'selected' : '' ?>>
                                            <?= esc($shopOption['shop_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Commission Settings Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator"></i> Commission Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= base_url('commission/update') ?>" id="commissionForm">
                        <?= csrf_field() ?>
                        <input type="hidden" name="shop_id" value="<?= $shop['shop_id'] ?? '' ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Barber Commission Rate</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" 
                                           class="form-control" id="barber_rate" name="barber_commission_rate" 
                                           value="<?= $settings['barber_commission_rate'] ?>" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Percentage that barber receives from each service</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Shop Commission Rate</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" max="100" 
                                           class="form-control" id="shop_rate" name="shop_commission_rate" 
                                           value="<?= $settings['shop_commission_rate'] ?>" required>
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Percentage that shop owner receives from each service</small>
                            </div>
                        </div>

                        <!-- Total Validation Display -->
                        <div class="row">
                            <div class="col-12 mb-3">
                                <div class="alert alert-info">
                                    <strong>Total: <span id="totalRate"><?= $settings['barber_commission_rate'] + $settings['shop_commission_rate'] ?></span>%</strong>
                                    <br>
                                    <small>The total must equal exactly 100%</small>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i> Update Commission Settings
                        </button>
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Example Calculation -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator"></i> Example Calculation
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>If a service costs ₱100:</strong></p>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Barber gets:</span>
                        <span class="fw-bold text-success">₱<span id="barberExample">70.00</span></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shop gets:</span>
                        <span class="fw-bold text-primary">₱<span id="shopExample">30.00</span></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong>₱100.00</strong>
                    </div>
                </div>
            </div>

            <!-- Quick Presets -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-magic"></i> Quick Presets
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setRates(70, 30)">
                            70% / 30% (Default)
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setRates(60, 40)">
                            60% / 40%
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setRates(80, 20)">
                            80% / 20%
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="setRates(50, 50)">
                            50% / 50%
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    updateCalculation();
    
    $('#barber_rate, #shop_rate').on('input', function() {
        updateCalculation();
    });
    
    $('#commissionForm').on('submit', function(e) {
        const total = parseFloat($('#barber_rate').val()) + parseFloat($('#shop_rate').val());
        if (Math.abs(total - 100) > 0.01) {
            e.preventDefault();
            alert('Commission rates must add up to exactly 100%');
        }
    });
});

function updateCalculation() {
    const barberRate = parseFloat($('#barber_rate').val()) || 0;
    const shopRate = parseFloat($('#shop_rate').val()) || 0;
    const total = barberRate + shopRate;
    
    $('#totalRate').text(total.toFixed(2));
    $('#barberExample').text((barberRate).toFixed(2));
    $('#shopExample').text((shopRate).toFixed(2));
    
    // Update button state
    const submitBtn = $('#submitBtn');
    if (Math.abs(total - 100) > 0.01) {
        submitBtn.prop('disabled', true);
        $('.alert-info').removeClass('alert-info').addClass('alert-warning');
    } else {
        submitBtn.prop('disabled', false);
        $('.alert-warning').removeClass('alert-warning').addClass('alert-info');
    }
}

function setRates(barberRate, shopRate) {
    $('#barber_rate').val(barberRate);
    $('#shop_rate').val(shopRate);
    updateCalculation();
}
</script>
<?= $this->endSection() ?>
