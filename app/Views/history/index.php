<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Add Info Box Styles -->
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
</style>

<div class="container-fluid px-4">
    <h1 class="mt-4">Haircut History</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">History</li>
    </ol>

    <!-- DASHBOARD SUMMARY -->
    <div class="row text-center mb-4">
        <div class="col-md-3 mb-2">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <h6 class="text-uppercase">Total Appointments</h6>
                    <h2><?= $total_appointments ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card bg-info text-white shadow-sm">
                <div class="card-body">
                    <h6 class="text-uppercase">History Records</h6>
                    <h2><?= count($history ?? []) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card bg-warning text-white shadow-sm">
                <div class="card-body">
                    <h6 class="text-uppercase">Average Rating</h6>
                    <h2><?= $average_rating ?? '0/5' ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <div class="card bg-success text-white shadow-sm">
                <div class="card-body">
                    <h6 class="text-uppercase">Total Customers</h6>
                    <h2><?= $total_customers ?? 0 ?></h2>
                </div>
            </div>
        </div>
    </div>



    <!-- HAIRCUT HISTORY -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">💇 Haircut History</h5>
            <?php if (session()->get('role') === 'barber'): ?>
            <a href="/history/create" class="btn btn-light btn-sm">
                <i class="fas fa-plus-circle"></i> Add New History
            </a>
            <?php endif; ?>
        </div>

        <div class="card-body bg-white">
            <!-- Filters -->
            <?php if (!empty($history)): ?>
            <div class="d-flex mb-3 flex-wrap gap-2">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by <?= session()->get('role') === 'barber' ? 'customer' : 'barber' ?>..." style="max-width: 250px;">
                <select id="styleFilter" class="form-select" style="max-width: 150px;">
                    <option value="">Filter by Style</option>
                    <?php 
                    $uniqueStyles = array_unique(array_column($history, 'style_name'));
                    foreach ($uniqueStyles as $style): 
                    ?>
                    <option value="<?= htmlspecialchars($style) ?>"><?= htmlspecialchars($style) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="ratingFilter" class="form-select" style="max-width: 150px;">
                    <option value="">Filter by Rating</option>
                    <option value="5">⭐⭐⭐⭐⭐ (5/5)</option>
                    <option value="4">⭐⭐⭐⭐ (4/5)</option>
                    <option value="3">⭐⭐⭐ (3/5)</option>
                    <option value="2">⭐⭐ (2/5)</option>
                    <option value="1">⭐ (1/5)</option>
                    <option value="0">No Rating</option>
                </select>
                <button class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <div class="ms-auto">
                    <button class="btn btn-success btn-sm" onclick="exportToExcel()"><i class="fas fa-file-excel"></i> Export Excel</button>
                    <button class="btn btn-danger btn-sm" onclick="exportToPDF()"><i class="fas fa-file-pdf"></i> Export PDF</button>
                </div>
            </div>
            <?php endif; ?>
            <?php if (empty($history)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No haircut history found</h5>
                    <p class="text-muted">
                        <?php if (session()->get('role') === 'barber'): ?>
                            Start adding haircut history to track your work.
                        <?php else: ?>
                            Your haircut history will appear here after appointments.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered text-center align-middle" id="historyTable">
                        <thead class="table-primary">
                            <tr>
                                <th>Date</th>
                                <th>Style</th>
                                <?php if (session()->get('role') === 'barber'): ?>
                                    <th>Customer</th>
                                <?php else: ?>
                                    <th>Barber</th>
                                <?php endif; ?>
                                <th>Cost</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $record): ?>
                            <tr>
                                <td>
                                    <?= date('M d, Y', strtotime($record['created_at'])) ?><br>
                                    <small class="text-muted"><?= date('h:i A', strtotime($record['created_at'])) ?></small>
                                </td>
                                <td><strong><?= esc($record['style_name']) ?></strong></td>
                                <td>
                                    <?php if (session()->get('role') === 'barber'): ?>
                                        <?= esc($record['customer_name'] ?? 'Unknown') ?>
                                    <?php else: ?>
                                        <?= esc($record['barber_name'] ?? 'Unknown') ?>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-success">₱<?= number_format($record['total_cost'], 2) ?></span></td>
                                <td>
                                    <?php if ($record['customer_rating']): ?>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?= $i <= $record['customer_rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                            <?php endfor; ?>
                                            <span class="ms-1"><?= $record['customer_rating'] ?>/5</span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No rating</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewHistory(<?= $record['history_id'] ?>)" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if (session()->get('role') === 'barber'): ?>
                                    <a href="/history/edit/<?= $record['history_id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" onclick="deleteHistory(<?= $record['history_id'] ?>)" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php elseif (session()->get('role') === 'customer' && session()->get('user_id') == $record['customer_id']): ?>
                                        <?php if (!$record['customer_rating']): ?>
                                            <a href="<?= base_url('history/rate/' . $record['history_id']) ?>" class="btn btn-sm btn-warning" title="Rate This Haircut">
                                                <i class="fas fa-star"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= base_url('history/rate/' . $record['history_id']) ?>" class="btn btn-sm btn-outline-warning" title="Update Rating">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
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

<!-- View Details Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Haircut Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewModalContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p>Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>

$(document).ready(function() {
    // Initialize DataTable
    $('#historyTable').DataTable({
        order: [[0, 'desc']], // Sort by date descending
        pageLength: 10,
        responsive: true
    });
    
    // Search input handler
    $('#searchInput').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('#historyTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
        });
    });
});

function applyFilters() {
    const searchTerm = $('#searchInput').val().toLowerCase();
    const styleFilter = $('#styleFilter').val();
    const ratingFilter = $('#ratingFilter').val();
    
    $('#historyTable tbody tr').each(function() {
        const $row = $(this);
        const rowText = $row.text().toLowerCase();
        const cells = $row.find('td');
        
        let show = true;
        
        // Apply search filter
        if (searchTerm && !rowText.includes(searchTerm)) {
            show = false;
        }
        
        // Apply style filter
        if (styleFilter && cells.eq(1).text().trim() !== styleFilter) {
            show = false;
        }
        
        // Apply rating filter
        if (ratingFilter !== '') {
            if (ratingFilter === '0') {
                // No rating filter
                if (cells.eq(4).text().includes('No rating') === false) {
                    show = false;
                }
            } else {
                // Has rating filter
                const ratingInRow = cells.eq(4).text();
                const starCount = (ratingInRow.match(/⭐/g) || []).length;
                if (starCount !== parseInt(ratingFilter)) {
                    show = false;
                }
            }
        }
        
        $row.toggle(show);
    });
}

function viewHistory(historyId) {
    $('#viewModalContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading...</p></div>');
    $('#viewModal').modal('show');
    
    $.get('/history/view/' + historyId, function(data) {
        $('#viewModalContent').html(data);
    }).fail(function() {
        $('#viewModalContent').html('<div class="alert alert-danger">Failed to load details.</div>');
    });
}

function exportToExcel() {
    // Simple CSV export
    const table = document.getElementById('historyTable');
    let csv = [];
    
    // Get headers
    const headers = [];
    $(table).find('thead th').each(function() {
        headers.push($(this).text().trim());
    });
    csv.push(headers.join(','));
    
    // Get data
    $(table).find('tbody tr:visible').each(function() {
        const row = [];
        $(this).find('td').each(function() {
            let text = $(this).text().trim();
            text = text.replace(/"/g, '""'); // Escape quotes
            row.push('"' + text + '"');
        });
        csv.push(row.join(','));
    });
    
    // Download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'haircut_history_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function exportToPDF() {
    window.print();
}

function deleteHistory(historyId) {
    if (confirm('Are you sure you want to delete this haircut history record?')) {
        window.location.href = `/history/delete/${historyId}`;
    }
}

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
