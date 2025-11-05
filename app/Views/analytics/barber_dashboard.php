<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
.info-box {
    display: block;
    min-height: 90px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 0.5rem;
    margin-bottom: 15px;
}
.info-box-icon {
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 0.5rem;
    display: block;
    float: left;
    height: 90px;
    width: 90px;
    text-align: center;
    font-size: 45px;
    line-height: 90px;
    background: rgba(0,0,0,0.2);
}
.info-box-content {
    padding: 15px 10px;
    margin-left: 90px;
}
.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 1.5rem;
}
.info-box-text {
    display: block;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.insight-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1rem;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h3 mb-0">📊 Analytics Dashboard</h1>
                    <p class="text-muted">Performance insights and business metrics</p>
                </div>
                <div>
                    <a href="<?= base_url('analytics/export-earnings') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-download"></i> Export PDF
                    </a>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart daily date picker -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4 col-xl-3">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <input type="date" id="start_date" class="form-control" value="<?= $end_date ?>">
                    <button id="btnToday" class="btn btn-outline-secondary">Today</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics with Info Box Style -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-calendar-day"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Daily Income</span>
                    <span class="info-box-number">₱<?= number_format($kpi['daily'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-calendar-week"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Weekly Income</span>
                    <span class="info-box-number">₱<?= number_format($kpi['weekly'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Monthly Income</span>
                    <span class="info-box-number">₱<?= number_format($kpi['monthly'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-star"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Customer Rating</span>
                    <span class="info-box-number"><?= $ratings['average_rating'] ?? 0 ?>/5</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="info-box" style="background: #fff;">
                <span class="info-box-icon" style="background: rgba(0,0,0,0.1);"><i class="fas fa-calendar-check" style="color: #007bff;"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Appointments</span>
                    <span class="info-box-number" style="color: #007bff;"><?= $earnings_stats['total_appointments'] ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="info-box" style="background: #fff;">
                <span class="info-box-icon" style="background: rgba(40,167,69,0.1);"><i class="fas fa-money-bill" style="color: #28a745;"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Avg per Appointment</span>
                    <span class="info-box-number" style="color: #28a745;">₱<?= number_format($earnings_stats['average_per_appointment'], 2) ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="info-box" style="background: #fff;">
                <span class="info-box-icon" style="background: rgba(255,193,7,0.1);"><i class="fas fa-chart-line" style="color: #ffc107;"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Earnings</span>
                    <span class="info-box-number" style="color: #ffc107;">₱<?= number_format($earnings_stats['total_earnings'], 2) ?></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3">
            <div class="info-box" style="background: #fff;">
                <span class="info-box-icon" style="background: rgba(23,162,184,0.1);"><i class="fas fa-chart-bar" style="color: #17a2b8;"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Yearly Projected</span>
                    <span class="info-box-number" style="color: #17a2b8;">₱<?= number_format($kpi['yearly'] ?? 0, 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2"><i class="fas fa-cut me-2"></i><strong>Popular Haircuts (Last 30 Days)</strong></div>
                    <canvas id="trendServices"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2"><i class="fas fa-comments me-2"></i><strong>Client Reviews</strong></div>
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $r): ?>
                            <div class="p-3 mb-2 rounded" style="background:#f7f9fc;">
                                <div class="mb-1 text-warning">
                                    <?php for ($i=0; $i<(int)$r['rating']; $i++) echo '<i class=\'fas fa-star\'></i>'; ?>
                                </div>
                                <div class="mb-1">"<?= esc($r['comment'] ?? '') ?>"</div>
                                <div class="small text-muted">— <?= esc($r['first_name']) ?> <?= substr(esc($r['last_name']),0,1) ?>.</div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">No reviews yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2"><i class="fas fa-chart-line me-2"></i><strong>Earnings Over Time (Last 12 Months)</strong></div>
                    <canvas id="earningsSeries"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Insights Summary -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="insight-card">
                <h6 class="mb-2"><i class="fas fa-lightbulb"></i> 💡 Business Insight</h6>
                <p class="mb-0">
                    <?php 
                    $weeklyAvg = $kpi['weekly'] > 0 ? number_format($kpi['weekly'] / 7, 2) : 0;
                    echo "You average ₱{$weeklyAvg} per day this week.";
                    ?>
                </p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="insight-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h6 class="mb-2"><i class="fas fa-trophy"></i> 🏆 Performance Score</h6>
                <p class="mb-0">
                    Based on <?= $earnings_stats['total_appointments'] ?> appointments, 
                    your rating is <?= $ratings['average_rating'] ?? 0 ?>/5 stars.
                </p>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="insight-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h6 class="mb-2"><i class="fas fa-rocket"></i> 📈 Growth Potential</h6>
                <p class="mb-0">
                    At current rate, you're on track to earn 
                    ₱<?= number_format($kpi['monthly'] * 12, 2) ?> this year.
                </p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
    // Date selection
    $('#btnToday').on('click', function(){
        const d = new Date();
        const v = d.toISOString().slice(0,10);
        $('#start_date').val(v);
        window.location.href = `<?= base_url('analytics') ?>?start_date=${v}&end_date=${v}`;
    });
    $('#start_date').on('change', function(){
        const v = $(this).val();
        window.location.href = `<?= base_url('analytics') ?>?start_date=${v}&end_date=${v}`;
    });

    // Charts with better labels and tooltips
    const serviceData = <?= json_encode($service_breakdown ?? []) ?>;
    const svcLabels = serviceData.map(s => s.service_name);
    const svcValues = serviceData.map(s => parseFloat(s.total_earnings));
    if (document.getElementById('trendServices')) {
        new Chart(document.getElementById('trendServices'), {
            type: 'bar',
            data: { 
                labels: svcLabels, 
                datasets: [{ 
                    label: 'Earnings (₱)', 
                    data: svcValues, 
                    backgroundColor: '#14b8a6',
                    borderColor: '#0d9488',
                    borderWidth: 1
                }] 
            },
            options: { 
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Revenue by Service Type'
                    }
                }, 
                scales: { 
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    const seriesData = <?= json_encode($earnings_series ?? []) ?>;
    const seriesLabels = seriesData.map(x => x.label);
    const seriesValues = seriesData.map(x => x.value);
    if (document.getElementById('earningsSeries')) {
        new Chart(document.getElementById('earningsSeries'), {
            type: 'line',
            data: { 
                labels: seriesLabels, 
                datasets: [{ 
                    label: '₱ Earnings', 
                    data: seriesValues, 
                    borderColor: '#6366f1', 
                    backgroundColor: 'rgba(99,102,241,.15)', 
                    fill: true, 
                    tension: .35 
                }] 
            },
            options: { 
                plugins: { 
                    tooltip: { 
                        callbacks: { 
                            label: (ctx) => `₱ ${Number(ctx.parsed.y).toLocaleString(undefined,{minimumFractionDigits:2})}` 
                        } 
                    },
                    title: {
                        display: true,
                        text: 'Earnings Trend (Last 12 Months)'
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    // Handle date filter form
    $('#dateFilterForm').submit(function(e) {
        e.preventDefault();
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        const serviceFilter = $('#service_filter').val();
        
        // Update page with new filters
        const params = new URLSearchParams({
            start_date: startDate,
            end_date: endDate
        });
        if (serviceFilter) params.append('service_filter', serviceFilter);
        
        // Reload page with new parameters
        window.location.href = `<?= base_url('analytics') ?>?${params}`;
    });
});
</script>
<?= $this->endSection() ?>
