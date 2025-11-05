<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mt-4">
                <i class="fas fa-magic text-primary"></i> Auto Schedule Generator
            </h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/schedule">Schedule</a></li>
                <li class="breadcrumb-item active">Auto Generator</li>
            </ol>
        </div>
        <div>
            <a href="/schedule" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Schedule
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Auto Generator Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs"></i> Generate Schedule Automatically
                    </h5>
                </div>
                <div class="card-body">
                    <form id="autoGeneratorForm">
                        <!-- Default Hours -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="default_start_time" class="form-label fw-bold">
                                    <i class="fas fa-clock text-primary"></i> Default Start Time
                                </label>
                                <input type="time" class="form-control" id="default_start_time" name="default_start_time" value="08:00">
                            </div>
                            <div class="col-md-6">
                                <label for="default_end_time" class="form-label fw-bold">
                                    <i class="fas fa-clock text-danger"></i> Default End Time
                                </label>
                                <input type="time" class="form-control" id="default_end_time" name="default_end_time" value="18:00">
                            </div>
                        </div>

                        <!-- Slot Duration - Hours & Minutes Input -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-stopwatch text-info"></i> Slot Duration
                                <span class="badge bg-primary ms-2" id="total-duration-display">30 minutes</span>
                            </label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-clock"></i> Hours
                                        </span>
                                        <input type="number" 
                                               class="form-control" 
                                               id="slot_duration_hours" 
                                               name="slot_duration_hours" 
                                               value="0" 
                                               min="0" 
                                               max="23"
                                               onchange="calculateDuration()"
                                               oninput="calculateDuration()">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-stopwatch"></i> Minutes
                                        </span>
                                        <input type="number" 
                                               class="form-control" 
                                               id="slot_duration_minutes" 
                                               name="slot_duration_minutes" 
                                               value="30" 
                                               min="0" 
                                               max="59"
                                               onchange="calculateDuration()"
                                               oninput="calculateDuration()">
                                    </div>
                                </div>
                            </div>
                            <div class="form-text mt-2">
                                <i class="fas fa-info-circle"></i> 
                                Type any duration you prefer (e.g., 0 hrs 8 mins, 1 hr 20 mins)
                            </div>
                            <!-- Hidden input for total minutes -->
                            <input type="hidden" id="slot_duration" name="slot_duration" value="30">
                        </div>

                        <!-- Date Range -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label fw-bold">
                                    <i class="fas fa-calendar text-success"></i> Start Date
                                </label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label fw-bold">
                                    <i class="fas fa-calendar text-warning"></i> End Date
                                </label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                            </div>
                        </div>

                        <!-- Days of Week -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-calendar-week text-primary"></i> Apply to Days
                            </label>
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

                        <!-- Break Times -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-coffee text-warning"></i> Break Times (Optional)
                            </label>
                            <div id="break-times">
                                <div class="row mb-2">
                                    <div class="col-md-5">
                                        <input type="time" class="form-control" name="break_start[]" placeholder="Start time">
                                    </div>
                                    <div class="col-md-5">
                                        <input type="time" class="form-control" name="break_end[]" placeholder="End time">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeBreakTime(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addBreakTime()">
                                <i class="fas fa-plus"></i> Add Break Time
                            </button>
                        </div>

                        <!-- Preview Button -->
                        <div class="text-center mb-4">
                            <button type="button" class="btn btn-info btn-lg" onclick="previewSchedule()">
                                <i class="fas fa-eye"></i> Preview Generated Schedule
                            </button>
                        </div>

                        <!-- Generate Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-magic"></i> Generate Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-eye"></i> Schedule Preview
                    </h5>
                </div>
                <div class="card-body">
                    <div id="schedule-preview">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                            <h6>No preview available</h6>
                            <p class="small">Click "Preview Generated Schedule" to see your schedule</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Templates -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-templates"></i> Quick Templates
                    </h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-outline-primary btn-sm mb-2 w-100" onclick="applyTemplate('weekday')">
                        <i class="fas fa-briefcase"></i> Weekday (9-5)
                    </button>
                    <button class="btn btn-outline-success btn-sm mb-2 w-100" onclick="applyTemplate('extended')">
                        <i class="fas fa-clock"></i> Extended (8-7)
                    </button>
                    <button class="btn btn-outline-warning btn-sm mb-2 w-100" onclick="applyTemplate('weekend')">
                        <i class="fas fa-calendar-weekend"></i> Weekend (10-4)
                    </button>
                    <button class="btn btn-outline-info btn-sm w-100" onclick="applyTemplate('flexible')">
                        <i class="fas fa-user-clock"></i> Flexible (8-6)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let breakTimeCount = 1;

// Calculate and display total duration in real-time
function calculateDuration() {
    const hours = parseInt(document.getElementById('slot_duration_hours').value) || 0;
    const minutes = parseInt(document.getElementById('slot_duration_minutes').value) || 0;
    
    // Calculate total minutes
    const totalMinutes = (hours * 60) + minutes;
    
    // Update hidden input
    document.getElementById('slot_duration').value = totalMinutes;
    
    // Update display badge
    const displayElement = document.getElementById('total-duration-display');
    if (hours === 0) {
        displayElement.textContent = `${totalMinutes} minutes`;
    } else if (minutes === 0) {
        displayElement.textContent = `${hours} ${hours === 1 ? 'hour' : 'hours'}`;
    } else {
        displayElement.textContent = `${hours} hrs ${minutes} mins (${totalMinutes} mins)`;
    }
    
    // Auto-update preview if it exists
    if (document.getElementById('schedule-preview').children[0]) {
        previewSchedule();
    }
}

function addBreakTime() {
    const container = document.getElementById('break-times');
    const breakTimeDiv = document.createElement('div');
    breakTimeDiv.className = 'row mb-2';
    breakTimeDiv.innerHTML = `
        <div class="col-md-5">
            <input type="time" class="form-control" name="break_start[]" placeholder="Start time">
        </div>
        <div class="col-md-5">
            <input type="time" class="form-control" name="break_end[]" placeholder="End time">
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeBreakTime(this)">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(breakTimeDiv);
    breakTimeCount++;
}

function removeBreakTime(button) {
    button.closest('.row').remove();
    breakTimeCount--;
}

function applyTemplate(template) {
    switch(template) {
        case 'weekday':
            document.getElementById('default_start_time').value = '09:00';
            document.getElementById('default_end_time').value = '17:00';
            document.getElementById('slot_duration_hours').value = '0';
            document.getElementById('slot_duration_minutes').value = '30';
            calculateDuration();
            // Check only weekdays
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'].forEach(day => {
                document.getElementById(day).checked = true;
            });
            ['saturday', 'sunday'].forEach(day => {
                document.getElementById(day).checked = false;
            });
            break;
        case 'extended':
            document.getElementById('default_start_time').value = '08:00';
            document.getElementById('default_end_time').value = '19:00';
            document.getElementById('slot_duration_hours').value = '0';
            document.getElementById('slot_duration_minutes').value = '30';
            calculateDuration();
            break;
        case 'weekend':
            document.getElementById('default_start_time').value = '10:00';
            document.getElementById('default_end_time').value = '16:00';
            document.getElementById('slot_duration_hours').value = '0';
            document.getElementById('slot_duration_minutes').value = '45';
            calculateDuration();
            // Check only weekends
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'].forEach(day => {
                document.getElementById(day).checked = false;
            });
            ['saturday', 'sunday'].forEach(day => {
                document.getElementById(day).checked = true;
            });
            break;
        case 'flexible':
            document.getElementById('default_start_time').value = '08:00';
            document.getElementById('default_end_time').value = '18:00';
            document.getElementById('slot_duration_hours').value = '0';
            document.getElementById('slot_duration_minutes').value = '30';
            calculateDuration();
            // Check all days
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'].forEach(day => {
                document.getElementById(day).checked = true;
            });
            break;
    }
}

function previewSchedule() {
    const formData = new FormData(document.getElementById('autoGeneratorForm'));
    const startTime = formData.get('default_start_time');
    const endTime = formData.get('default_end_time');
    const slotDuration = parseInt(formData.get('slot_duration'));
    const startDate = formData.get('start_date');
    const endDate = formData.get('end_date');
    const selectedDays = formData.getAll('days[]');
    
    // Generate preview
    let previewHtml = '<div class="schedule-preview-content">';
    previewHtml += `<h6>Schedule Summary:</h6>`;
    previewHtml += `<p><strong>Time:</strong> ${startTime} - ${endTime}</p>`;
    previewHtml += `<p><strong>Duration:</strong> ${slotDuration} minutes per slot</p>`;
    previewHtml += `<p><strong>Period:</strong> ${startDate} to ${endDate}</p>`;
    previewHtml += `<p><strong>Days:</strong> ${selectedDays.join(', ')}</p>`;
    
    // Calculate total slots
    const start = new Date(`2000-01-01 ${startTime}`);
    const end = new Date(`2000-01-01 ${endTime}`);
    const duration = (end - start) / (1000 * 60); // minutes
    const slotsPerDay = Math.floor(duration / slotDuration);
    
    // Calculate date range
    const startDateObj = new Date(startDate);
    const endDateObj = new Date(endDate);
    const daysDiff = Math.ceil((endDateObj - startDateObj) / (1000 * 60 * 60 * 24));
    
    // Count selected days in range
    let totalSlots = 0;
    for (let i = 0; i <= daysDiff; i++) {
        const currentDate = new Date(startDateObj);
        currentDate.setDate(startDateObj.getDate() + i);
        const dayName = currentDate.toLocaleDateString('en-US', { weekday: 'lowercase' });
        if (selectedDays.includes(dayName)) {
            totalSlots += slotsPerDay;
        }
    }
    
    previewHtml += `<p><strong>Total Slots:</strong> ${totalSlots}</p>`;
    previewHtml += `<p><strong>Slots per Day:</strong> ${slotsPerDay}</p>`;
    previewHtml += '</div>';
    
    document.getElementById('schedule-preview').innerHTML = previewHtml;
}

// Form submission
document.getElementById('autoGeneratorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    submitBtn.disabled = true;
    
    // Send to server
    fetch('/CleanCut/schedule/generate-auto', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Schedule Generated!',
                text: data.message,
                timer: 3000
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to generate schedule'
        });
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set default end date to 7 days from now
    const today = new Date();
    const nextWeek = new Date(today);
    nextWeek.setDate(today.getDate() + 7);
    document.getElementById('end_date').value = nextWeek.toISOString().split('T')[0];
    
    // Calculate initial duration
    calculateDuration();
});
</script>

<style>
.schedule-preview-content {
    font-size: 0.9rem;
}

.schedule-preview-content p {
    margin-bottom: 0.5rem;
}

.form-check {
    margin-bottom: 0.5rem;
}

.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-warning:hover,
.btn-outline-info:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Enhanced styling for duration inputs */
#slot_duration_hours,
#slot_duration_minutes {
    font-size: 1.1rem;
    font-weight: 600;
    text-align: center;
    border: 2px solid #007bff;
    transition: all 0.3s ease;
}

#slot_duration_hours:focus,
#slot_duration_minutes:focus {
    border-color: #0056b3;
    box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
    transform: scale(1.05);
}

#total-duration-display {
    font-size: 1rem;
    padding: 0.5rem 1rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

/* Input group styling */
.input-group {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 0.375rem;
}

.input-group-text {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    font-weight: 600;
    border: none;
}
</style>
<?= $this->endSection() ?>
