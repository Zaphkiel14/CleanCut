<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mt-4">
                <i class="fas fa-chart-line text-primary"></i> Analytics Dashboard
            </h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Analytics</li>
            </ol>
        </div>
        <div>
            <button class="btn btn-success" onclick="exportAnalytics()">
                <i class="fas fa-download"></i> Export Data
            </button>
            <button class="btn btn-primary" onclick="refreshAnalytics()">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter"></i> Filter by Date Range
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" 
                           value="<?= date('Y-m-01') ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" 
                           value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label for="barber_filter" class="form-label">Barber</label>
                    <select class="form-select" id="barber_filter">
                        <option value="">All Barbers</option>
                        <!-- Barbers will be loaded here -->
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100" onclick="applyFilters()">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Total Appointments</div>
                            <div class="h4" id="total-appointments">0</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Completed</div>
                            <div class="h4" id="completed-appointments">0</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Cancelled</div>
                            <div class="h4" id="cancelled-appointments">0</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small text-white-50">Total Revenue</div>
                            <div class="h4" id="total-revenue">₱0</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Appointments Over Time -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line"></i> Appointments Over Time
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="appointmentsChart" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Service Distribution -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Service Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="servicesChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Analytics -->
    <div class="row">
        <!-- Barber Performance -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user-tie"></i> Barber Performance
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Barber</th>
                                    <th>Appointments</th>
                                    <th>Revenue</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody id="barber-performance">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-history"></i> Recent Activity
                    </h6>
                </div>
                <div class="card-body">
                    <div id="recent-activity">
                        <!-- Activity will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Reports -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-table"></i> Detailed Reports
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="analytics-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Barber</th>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let appointmentsChart, servicesChart;

// Initialize analytics on page load
document.addEventListener('DOMContentLoaded', function() {
    loadBarbers();
    loadAnalytics();
    initializeCharts();
});

// Load barbers for filter
function loadBarbers() {
    fetch('/CleanCut/analytics/barbers')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('barber_filter');
            data.barbers.forEach(barber => {
                const option = document.createElement('option');
                option.value = barber.user_id;
                option.textContent = `${barber.first_name} ${barber.last_name}`;
                select.appendChild(option);
            });
        }
    })
    .catch(error => console.error('Error loading barbers:', error));
}

// Load analytics data
function loadAnalytics() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const barberId = document.getElementById('barber_filter').value;

    fetch('/CleanCut/analytics/data', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            start_date: startDate,
            end_date: endDate,
            barber_id: barberId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateMetrics(data.metrics);
            updateCharts(data.chartData);
            updateTables(data.tableData);
        }
    })
    .catch(error => console.error('Error loading analytics:', error));
}

// Update key metrics
function updateMetrics(metrics) {
    document.getElementById('total-appointments').textContent = metrics.total_appointments;
    document.getElementById('completed-appointments').textContent = metrics.completed_appointments;
    document.getElementById('cancelled-appointments').textContent = metrics.cancelled_appointments;
    document.getElementById('total-revenue').textContent = '₱' + metrics.total_revenue.toLocaleString();
}

// Initialize charts
function initializeCharts() {
    // Appointments over time chart
    const appointmentsCtx = document.getElementById('appointmentsChart').getContext('2d');
    appointmentsChart = new Chart(appointmentsCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Appointments',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Services distribution chart
    const servicesCtx = document.getElementById('servicesChart').getContext('2d');
    servicesChart = new Chart(servicesCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

// Update charts with new data
function updateCharts(chartData) {
    // Update appointments chart
    appointmentsChart.data.labels = chartData.appointments_over_time.labels;
    appointmentsChart.data.datasets[0].data = chartData.appointments_over_time.data;
    appointmentsChart.update();

    // Update services chart
    servicesChart.data.labels = chartData.services_distribution.labels;
    servicesChart.data.datasets[0].data = chartData.services_distribution.data;
    servicesChart.update();
}

// Update tables with new data
function updateTables(tableData) {
    // Update barber performance table
    const barberPerformance = document.getElementById('barber-performance');
    barberPerformance.innerHTML = '';
    
    tableData.barber_performance.forEach(barber => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${barber.name}</td>
            <td>${barber.appointments}</td>
            <td>₱${barber.revenue.toLocaleString()}</td>
            <td>${barber.rating}/5</td>
        `;
        barberPerformance.appendChild(row);
    });

    // Update recent activity
    const recentActivity = document.getElementById('recent-activity');
    recentActivity.innerHTML = '';
    
    tableData.recent_activity.forEach(activity => {
        const activityDiv = document.createElement('div');
        activityDiv.className = 'd-flex align-items-center mb-3';
        activityDiv.innerHTML = `
            <div class="me-3">
                <i class="fas fa-${getActivityIcon(activity.type)} text-primary"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold">${activity.title}</div>
                <small class="text-muted">${activity.time}</small>
            </div>
        `;
        recentActivity.appendChild(activityDiv);
    });

    // Update detailed reports table
    const analyticsTable = document.getElementById('analytics-table').getElementsByTagName('tbody')[0];
    analyticsTable.innerHTML = '';
    
    tableData.detailed_reports.forEach(report => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${report.date}</td>
            <td>${report.barber}</td>
            <td>${report.customer}</td>
            <td>${report.service}</td>
            <td><span class="badge bg-${getStatusColor(report.status)}">${report.status}</span></td>
            <td>₱${report.amount.toLocaleString()}</td>
            <td>${report.duration} min</td>
        `;
        analyticsTable.appendChild(row);
    });
}

// Get activity icon based on type
function getActivityIcon(type) {
    switch(type) {
        case 'booking': return 'calendar-plus';
        case 'cancellation': return 'calendar-times';
        case 'completion': return 'check-circle';
        default: return 'bell';
    }
}

// Get status color for badges
function getStatusColor(status) {
    switch(status.toLowerCase()) {
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        case 'pending': return 'warning';
        case 'confirmed': return 'info';
        default: return 'secondary';
    }
}

// Apply filters
function applyFilters() {
    loadAnalytics();
}

// Refresh analytics
function refreshAnalytics() {
    loadAnalytics();
}

// Export analytics data
function exportAnalytics() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const barberId = document.getElementById('barber_filter').value;

    const url = `/CleanCut/analytics/export?start_date=${startDate}&end_date=${endDate}&barber_id=${barberId}`;
    window.open(url, '_blank');
}
</script>

<style>
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #5a5c69;
}

.badge {
    font-size: 0.75em;
}

#analytics-table {
    font-size: 0.9rem;
}

.chart-container {
    position: relative;
    height: 300px;
}
</style>
<?= $this->endSection() ?>
