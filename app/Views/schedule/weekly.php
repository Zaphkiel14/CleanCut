<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mt-4">
                <i class="fas fa-calendar-week text-primary"></i> Weekly Schedule
            </h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/schedule">Schedule</a></li>
                <li class="breadcrumb-item active">Weekly View</li>
            </ol>
        </div>
        <div>
            <button class="btn btn-primary" onclick="previousWeek()">
                <i class="fas fa-chevron-left"></i> Previous Week
            </button>
            <button class="btn btn-primary" onclick="nextWeek()">
                Next Week <i class="fas fa-chevron-right"></i>
            </button>
            <a href="/schedule" class="btn btn-secondary">
                <i class="fas fa-calendar-day"></i> Daily View
            </a>
        </div>
    </div>

    <!-- Week Navigation -->
    <div class="card mb-4">
        <div class="card-body text-center">
            <h4 id="current-week-display" class="mb-0"></h4>
            <small class="text-muted">Click on any day to view detailed schedule</small>
        </div>
    </div>

    <!-- Weekly Calendar Grid -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> Weekly Schedule Overview
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="weekly-calendar">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center" style="width: 120px;">Time</th>
                                    <th class="text-center">Monday</th>
                                    <th class="text-center">Tuesday</th>
                                    <th class="text-center">Wednesday</th>
                                    <th class="text-center">Thursday</th>
                                    <th class="text-center">Friday</th>
                                    <th class="text-center">Saturday</th>
                                    <th class="text-center">Sunday</th>
                                </tr>
                            </thead>
                            <tbody id="weekly-schedule-body">
                                <!-- Time slots will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-tools"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-success btn-sm mb-2" onclick="setWeeklySchedule()">
                        <i class="fas fa-copy"></i> Set Same Schedule for Week
                    </button>
                    <br>
                    <button class="btn btn-info btn-sm mb-2" onclick="copyToNextWeek()">
                        <i class="fas fa-forward"></i> Copy to Next Week
                    </button>
                    <br>
                    <button class="btn btn-warning btn-sm" onclick="clearWeek()">
                        <i class="fas fa-trash"></i> Clear Week
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Week Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div id="week-summary">
                        <p class="mb-1"><strong>Total Hours:</strong> <span id="total-hours">0</span></p>
                        <p class="mb-1"><strong>Available Slots:</strong> <span id="available-slots">0</span></p>
                        <p class="mb-0"><strong>Booked Slots:</strong> <span id="booked-slots">0</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Set Weekly Schedule Modal -->
<div class="modal fade" id="weeklyScheduleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-week"></i> Set Weekly Schedule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="weeklyScheduleForm">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="start_time" name="start_time" value="08:00">
                        </div>
                        <div class="col-md-6">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="end_time" name="end_time" value="18:00">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Apply to Days:</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="monday" name="days[]" value="monday" checked>
                                    <label class="form-check-label" for="monday">Monday</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="tuesday" name="days[]" value="tuesday" checked>
                                    <label class="form-check-label" for="tuesday">Tuesday</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="wednesday" name="days[]" value="wednesday" checked>
                                    <label class="form-check-label" for="wednesday">Wednesday</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="thursday" name="days[]" value="thursday" checked>
                                    <label class="form-check-label" for="thursday">Thursday</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="friday" name="days[]" value="friday" checked>
                                    <label class="form-check-label" for="friday">Friday</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="saturday" name="days[]" value="saturday">
                                    <label class="form-check-label" for="saturday">Saturday</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sunday" name="days[]" value="sunday">
                                    <label class="form-check-label" for="sunday">Sunday</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="applyWeeklySchedule()">
                    <i class="fas fa-check"></i> Apply Schedule
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let currentWeekStart = new Date();
let currentWeekEnd = new Date();

// Initialize current week
function initializeWeek() {
    const today = new Date();
    const dayOfWeek = today.getDay();
    const diff = today.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1); // Monday
    currentWeekStart = new Date(today.setDate(diff));
    currentWeekEnd = new Date(currentWeekStart);
    currentWeekEnd.setDate(currentWeekStart.getDate() + 6);
    
    updateWeekDisplay();
    loadWeeklySchedule();
}

function updateWeekDisplay() {
    const startStr = currentWeekStart.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: 'numeric'
    });
    const endStr = currentWeekEnd.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: 'numeric'
    });
    
    document.getElementById('current-week-display').textContent = 
        `Week of ${startStr} - ${endStr}`;
}

function previousWeek() {
    currentWeekStart.setDate(currentWeekStart.getDate() - 7);
    currentWeekEnd.setDate(currentWeekEnd.getDate() - 7);
    updateWeekDisplay();
    loadWeeklySchedule();
}

function nextWeek() {
    currentWeekStart.setDate(currentWeekStart.getDate() + 7);
    currentWeekEnd.setDate(currentWeekEnd.getDate() + 7);
    updateWeekDisplay();
    loadWeeklySchedule();
}

function loadWeeklySchedule() {
    // Generate time slots from 8 AM to 6 PM
    const timeSlots = [];
    for (let hour = 8; hour < 18; hour++) {
        for (let minute = 0; minute < 60; minute += 30) {
            const time = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
            timeSlots.push(time);
        }
    }
    
    const tbody = document.getElementById('weekly-schedule-body');
    tbody.innerHTML = '';
    
    timeSlots.forEach(time => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-center fw-bold">${formatTime(time)}</td>
            <td class="day-cell" data-day="monday" data-time="${time}"></td>
            <td class="day-cell" data-day="tuesday" data-time="${time}"></td>
            <td class="day-cell" data-day="wednesday" data-time="${time}"></td>
            <td class="day-cell" data-day="thursday" data-time="${time}"></td>
            <td class="day-cell" data-day="friday" data-time="${time}"></td>
            <td class="day-cell" data-day="saturday" data-time="${time}"></td>
            <td class="day-cell" data-day="sunday" data-time="${time}"></td>
        `;
        tbody.appendChild(row);
    });
    
    // Load actual schedule data
    loadScheduleData();
}

function formatTime(time) {
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const period = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour === 0 ? 12 : (hour > 12 ? hour - 12 : hour);
    return `${displayHour}:${minutes} ${period}`;
}

function loadScheduleData() {
    // This would load actual schedule data from the server
    // For now, we'll simulate some data
    const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    
    days.forEach(day => {
        const cells = document.querySelectorAll(`[data-day="${day}"]`);
        cells.forEach(cell => {
            // Simulate some availability
            if (Math.random() > 0.3) {
                cell.classList.add('available');
                cell.innerHTML = '<i class="fas fa-check text-success"></i>';
                cell.title = 'Available';
            } else {
                cell.classList.add('booked');
                cell.innerHTML = '<i class="fas fa-times text-danger"></i>';
                cell.title = 'Booked';
            }
        });
    });
    
    updateWeekSummary();
}

function updateWeekSummary() {
    const available = document.querySelectorAll('.available').length;
    const booked = document.querySelectorAll('.booked').length;
    const total = available + booked;
    
    document.getElementById('available-slots').textContent = available;
    document.getElementById('booked-slots').textContent = booked;
    document.getElementById('total-hours').textContent = (total * 0.5).toFixed(1); // 30-minute slots
}

function setWeeklySchedule() {
    $('#weeklyScheduleModal').modal('show');
}

function applyWeeklySchedule() {
    const formData = new FormData(document.getElementById('weeklyScheduleForm'));
    const startTime = formData.get('start_time');
    const endTime = formData.get('end_time');
    const selectedDays = formData.getAll('days[]');
    
    // Apply schedule to selected days
    selectedDays.forEach(day => {
        // This would send data to server
        console.log(`Setting ${day} schedule: ${startTime} - ${endTime}`);
    });
    
    $('#weeklyScheduleModal').modal('hide');
    loadWeeklySchedule();
}

function copyToNextWeek() {
    if (confirm('Copy this week\'s schedule to next week?')) {
        // Implementation for copying schedule
        console.log('Copying schedule to next week');
    }
}

function clearWeek() {
    if (confirm('Clear all schedules for this week?')) {
        // Implementation for clearing schedule
        console.log('Clearing week schedule');
        loadWeeklySchedule();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeWeek();
});
</script>

<style>
.day-cell {
    height: 40px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.day-cell:hover {
    background-color: #f8f9fa;
}

.day-cell.available {
    background-color: #d4edda;
}

.day-cell.booked {
    background-color: #f8d7da;
}

.day-cell.unavailable {
    background-color: #e2e3e5;
}

.table th {
    background-color: #343a40;
    color: white;
    border: none;
}

.table td {
    border: 1px solid #dee2e6;
    vertical-align: middle;
}
</style>
<?= $this->endSection() ?>
