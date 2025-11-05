<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-chart-line"></i> Shop Analytics Dashboard
                </h1>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('analytics/export-earnings') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-download"></i> Export Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Shop Info Card -->
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

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="dateFilterForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="<?= $start_date ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="<?= $end_date ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="barber_filter" class="form-label">Barber Filter</label>
                            <select class="form-select" id="barber_filter" name="barber_filter">
                                <option value="">All Barbers</option>
                                <?php if (!empty($barbers)): ?>
                                    <?php foreach ($barbers as $barber): ?>
                                        <option value="<?= $barber['user_id'] ?>">
                                            <?= esc($barber['first_name'] . ' ' . $barber['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card earnings-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white">Total Revenue</h6>
                            <h3 class="text-white mb-0">₱<?= number_format($overall_stats['total_earnings'] ?? 0, 2) ?></h3>
                        </div>
                        <div class="text-white">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card appointments-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white">Total Appointments</h6>
                            <h3 class="text-white mb-0"><?= $overall_stats['total_appointments'] ?? 0 ?></h3>
                        </div>
                        <div class="text-white">
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card analytics-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white">Active Barbers</h6>
                            <h3 class="text-white mb-0"><?= count($barbers ?? []) ?></h3>
                        </div>
                        <div class="text-white">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card customers-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-white">Avg per Appointment</h6>
                            <h3 class="text-white mb-0">₱<?= number_format($overall_stats['average_per_appointment'] ?? 0, 2) ?></h3>
                        </div>
                        <div class="text-white">
                            <i class="fas fa-chart-bar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barber Performance -->
    <?php if (!empty($barber_performance)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Barber Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Barber</th>
                                    <th>Total Earnings</th>
                                    <th>Appointments</th>
                                    <th>Avg per Appointment</th>
                                    <th>Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($barber_performance as $performance): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($performance['barber_name']) ?></strong>
                                        </td>
                                        <td>₱<?= number_format($performance['total_earnings'], 2) ?></td>
                                        <td><?= $performance['total_appointments'] ?></td>
                                        <td>₱<?= number_format($performance['average_per_appointment'], 2) ?></td>
                                        <td>₱<?= number_format($performance['commission'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Service Popularity -->
    <?php if (!empty($service_popularity)): ?>
    <div class="row mb-4">
        <div class="col-xl-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Revenue Trend
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Service Popularity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th class="text-end">Bookings</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($service_popularity as $service): ?>
                                    <tr>
                                        <td><?= esc($service['service_name']) ?></td>
                                        <td class="text-end"><?= $service['booking_count'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // Handle date filter form
    $('#dateFilterForm').submit(function(e) {
        e.preventDefault();
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const barberFilter = $('#barber_filter').val();

        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (barberFilter) params.append('barber_filter', barberFilter);

        window.location.href = '<?= base_url('analytics') ?>?' + params.toString();
    });

    // Initialize revenue chart if we have data
    <?php if (!empty($overall_stats)): ?>
    initializeRevenueChart();
    <?php endif; ?>
});

function initializeRevenueChart() {
    // This would ideally load data via AJAX, but for now show a simple placeholder
    const ctx = document.getElementById('revenueChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Daily Revenue',
                    data: [0, 0, 0, 0, 0, 0, 0], // Placeholder data
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    }
}
</script>
<?= $this->endSection() ?>
