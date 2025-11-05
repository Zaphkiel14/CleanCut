<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CleanCut - Barber Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .main-content {
            background-color: #ffffff;
            min-height: 100vh;
        }
        
        .stats-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .schedule-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .time-slot {
            background-color: var(--success-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            margin: 4px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .time-slot:hover {
            background-color: #059669;
            transform: scale(1.05);
        }
        
        .time-slot.booked {
            background-color: #6b7280;
            cursor: not-allowed;
        }
        
        .profile-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .alert-success {
            background-color: #d1fae5;
            border-color: #a7f3d0;
            color: #065f46;
            border-radius: 12px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            font-weight: 500;
        }
        
        .search-box {
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 10px 16px;
        }
        
        .search-box:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-4">
                    <div class="d-flex align-items-center mb-4">
                        <i class="fas fa-cut me-2 fs-4"></i>
                        <h4 class="mb-0 fw-bold">CleanCut</h4>
                    </div>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="#">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="#">
                            <i class="fas fa-calendar-alt me-2"></i> Appointments
                        </a>
                        <a class="nav-link" href="#">
                            <i class="fas fa-clock me-2"></i> Schedule
                        </a>
                        <a class="nav-link" href="#">
                            <i class="fas fa-users me-2"></i> Clients
                        </a>
                        <a class="nav-link" href="#">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a>
                        <a class="nav-link" href="#">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content p-0">
                <div class="p-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="fw-bold mb-1">Hello, Barber!</h2>
                            <p class="text-muted mb-0">Welcome to your CleanCut dashboard.</p>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="position-relative">
                                <input type="text" class="form-control search-box" placeholder="Search here..." style="width: 250px;">
                                <i class="fas fa-search position-absolute" style="right: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                            </div>
                            <div class="profile-avatar">B</div>
                            <div>
                                <div class="fw-bold">Barber</div>
                                <small class="text-muted">Barber</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Success Alert -->
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        Schedule updated!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <div class="card-body text-center p-4">
                                    <div class="d-flex justify-content-center mb-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #dbeafe;">
                                            <i class="fas fa-calendar-check text-primary fs-4"></i>
                                        </div>
                                    </div>
                                    <h3 class="fw-bold mb-1"><?= count($today_appointments ?? []) ?></h3>
                                    <p class="text-muted mb-0">Today's Appointments</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <div class="card-body text-center p-4">
                                    <div class="d-flex justify-content-center mb-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #dcfce7;">
                                            <i class="fas fa-file-alt text-success fs-4"></i>
                                        </div>
                                    </div>
                                    <h3 class="fw-bold mb-1"><?= $recent_posts ?? 0 ?></h3>
                                    <p class="text-muted mb-0">Recent Posts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <div class="card-body text-center p-4">
                                    <div class="d-flex justify-content-center mb-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #fef3c7;">
                                            <i class="fas fa-bell text-warning fs-4"></i>
                                        </div>
                                    </div>
                                    <h3 class="fw-bold mb-1"><?= $notifications ?? 0 ?></h3>
                                    <p class="text-muted mb-0">Notifications</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card stats-card">
                                <div class="card-body text-center p-4">
                                    <div class="d-flex justify-content-center mb-3">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; background-color: #fee2e2;">
                                            <i class="fas fa-clock text-danger fs-4"></i>
                                        </div>
                                    </div>
                                    <h3 class="fw-bold mb-1"><?= $available_slots ?? 12 ?></h3>
                                    <p class="text-muted mb-0">Available Slots</p>
                                </div>
                            </div>
                        </div>
                    </div>

<!-- Calendar & Time Slot Management -->
<div class="card schedule-card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3"><i class="fas fa-calendar-alt me-2"></i>Manage Your Schedule</h5>
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="schedule-date" class="form-label">Select Date</label>
                <input type="date" id="schedule-date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-8 d-flex align-items-end">
                <button id="select-all-slots" class="btn btn-sm btn-outline-success me-2">Select All</button>
                <button id="clear-all-slots" class="btn btn-sm btn-outline-secondary">Clear All</button>
            </div>
        </div>
        <div id="time-slots" class="mb-3 d-flex flex-wrap">
            <!-- Time slots will be generated by JS -->
        </div>
        <button id="save-availability" class="btn btn-primary">Save Availability</button>
        <div id="save-status" class="mt-2"></div>
    </div>
</div>
<script>
const slotStart = 8 * 60; // 08:00 in minutes
const slotEnd = 19 * 60; // 19:00 in minutes
const slotDuration = 30; // 30 minutes

function pad(n) { return n < 10 ? '0' + n : n; }

function generateSlots(selected=[]) {
    const container = document.getElementById('time-slots');
    container.innerHTML = '';
    for (let mins = slotStart; mins <= slotEnd; mins += slotDuration) {
        const h = Math.floor(mins / 60);
        const m = mins % 60;
        const time = pad(h) + ':' + pad(m);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'time-slot btn btn-sm' + (selected.includes(time) ? ' active' : '');
        btn.textContent = time;
        btn.dataset.time = time;
        btn.onclick = function() {
            btn.classList.toggle('active');
        };
        container.appendChild(btn);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    generateSlots();

    document.getElementById('select-all-slots').onclick = function() {
        document.querySelectorAll('.time-slot').forEach(btn => btn.classList.add('active'));
    };
    document.getElementById('clear-all-slots').onclick = function() {
        document.querySelectorAll('.time-slot').forEach(btn => btn.classList.remove('active'));
    };

    document.getElementById('save-availability').onclick = function() {
        const date = document.getElementById('schedule-date').value;
        const slots = Array.from(document.querySelectorAll('.time-slot.active')).map(btn => btn.dataset.time);
        fetch('/CleanCut/barber/update-availability', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ date, slots })
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('save-status').innerHTML = '<span class="text-success">' + (data.success ? 'Availability updated!' : 'Failed to update') + '</span>';
        })
        .catch(() => {
            document.getElementById('save-status').innerHTML = '<span class="text-danger">Error saving availability.</span>';
        });
    };

    document.getElementById('schedule-date').onchange = function() {
        const date = this.value;
        fetch('/CleanCut/barber/get-availability?date=' + encodeURIComponent(date))
            .then(res => res.json())
            .then(data => {
                generateSlots(data.slots || []);
            });
    };
});
</script>
