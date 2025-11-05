<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-1">
                <i class="fas fa-calendar-check text-primary"></i>
                <?= $user_role === 'barber' ? 'Schedule Management' : 'Shop Schedule Overview' ?>
            </h3>
            <p class="text-muted">Manage your availability and time slots</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($user_role === 'barber'): ?>
        <!-- Selected Day's Available Timeslots -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-day me-2"></i>Available Timeslots for <span id="slots-title-date"></span>
                </h5>
                <div class="d-flex gap-2">
                    <button id="edit-today-slots" class="btn btn-sm btn-light text-primary" style="display: none;">
                        <i class="fas fa-edit me-1"></i>Edit Selected Day
                    </button>
                    <button id="save-today-slots" class="btn btn-sm btn-success" style="display: none;">
                        <i class="fas fa-save me-1"></i>Save
                    </button>
                    <button id="cancel-today-edit" class="btn btn-sm btn-outline-light" style="display: none;">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Display filters -->
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-sm-6 col-md-4">
                        <label class="form-label">View</label>
                        <select id="display-mode" class="form-select">
                            <option value="today" selected>Today</option>
                            <option value="week">This Week</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <label class="form-label">Display Interval</label>
                        <select id="display-interval" class="form-select">
                            <option value="15">15 min</option>
                            <option value="30" selected>30 min</option>
                            <option value="45">45 min</option>
                            <option value="60">60 min</option>
                            <option value="90">1 hr 30 min</option>
                        </select>
                    </div>
                </div>
                <div id="today-timeslots">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                        <p>Loading availability...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Schedule Management Card -->
        <div class="card shadow-lg mb-4 border-0">
            <div class="card-body p-4">
                <!-- Date Selection Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="schedule-date" class="form-label fw-bold mb-2">
                            <i class="fas fa-calendar text-primary me-2"></i>Select Date
                        </label>
                        <input type="date" id="schedule-date" class="form-control form-control-lg" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <!-- Mode Tabs -->
                <ul class="nav nav-tabs mb-4" id="scheduleTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="manual-tab" data-bs-toggle="tab" data-bs-target="#manual" type="button" role="tab">
                            <i class="fas fa-hand-pointer me-2"></i>Manual Mode
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="auto-tab" data-bs-toggle="tab" data-bs-target="#auto" type="button" role="tab">
                            <i class="fas fa-magic me-2"></i>Automated Mode
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="scheduleTabsContent">
                    <!-- MANUAL MODE -->
                    <div class="tab-pane fade show active" id="manual" role="tabpanel" aria-labelledby="manual-tab">
                        <h6 class="fw-bold mb-3 text-primary">
                            <i class="fas fa-clock me-2"></i>Manual Time Entry
                        </h6>

                        <div class="row mb-4">
                            <div class="col-md-10">
                                <input type="time" id="manual-time" class="form-control form-control-lg" placeholder="Select time">
                            </div>
                            <div class="col-md-2">
                                <button id="add-manual-time" class="btn btn-primary w-100" style="height: 48px;">
                                    <i class="fas fa-plus"></i>Add
                                </button>
                            </div>
                        </div>

                        <!-- Quick Add Buttons -->
                        <div class="mb-3">
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-bolt me-1"></i>Or click to add quickly:
                            </small>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickAddTime('08:00')">
                                    <i class="fas fa-clock me-1"></i>8 AM
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickAddTime('09:00')">
                                    <i class="fas fa-clock me-1"></i>9 AM
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickAddTime('10:00')">
                                    <i class="fas fa-clock me-1"></i>10 AM
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickAddTime('11:00')">
                                    <i class="fas fa-clock me-1"></i>11 AM
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickAddTime('12:00')">
                                    <i class="fas fa-clock me-1"></i>12 PM
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickAddTime('13:00')">
                                    <i class="fas fa-clock me-1"></i>1 PM
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickAddTime('14:00')">
                                    <i class="fas fa-clock me-1"></i>2 PM
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickAddTime('15:00')">
                                    <i class="fas fa-clock me-1"></i>3 PM
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickAddTime('16:00')">
                                    <i class="fas fa-clock me-1"></i>4 PM
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="quickAddTime('17:00')">
                                    <i class="fas fa-clock me-1"></i>5 PM
                                </button>
                            </div>
                        </div>

                        <!-- Interval Generator -->
                        <div class="mb-4">
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-sliders-h me-1"></i>Generate by interval:
                            </small>
                            <div class="row g-2 align-items-end">
                                <div class="col-sm-4 col-md-3">
                                    <label class="form-label">Start</label>
                                    <input type="time" id="interval-start" class="form-control" value="08:00">
                                </div>
                                <div class="col-sm-4 col-md-3">
                                    <label class="form-label">End</label>
                                    <input type="time" id="interval-end" class="form-control" value="18:00">
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="generateIntervalSlots(15)">15 mins</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="generateIntervalSlots(30)">30 mins</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="generateIntervalSlots(60)">1 hr</button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="generateIntervalSlots(90)">1 hr 30 mins</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Your Selected Times -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2 text-info">
                                <i class="fas fa-list-ul me-2"></i>Your Available Times (<span id="slot-count">0</span>)
                            </h6>
                            <div class="bg-light p-3 rounded border" id="time-slots" style="min-height: 60px;">
                                <div class="text-center text-muted py-3" id="no-slots-message">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                                    <p><small>No time slots added yet</small></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AUTOMATED MODE -->
                    <div class="tab-pane fade" id="auto" role="tabpanel" aria-labelledby="auto-tab">
                        <h6 class="fw-bold mb-3 text-primary">
                            <i class="fas fa-robot me-2"></i>Auto Generate Schedule
                        </h6>

                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" id="auto-start" class="form-control form-control-lg" value="08:00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Time</label>
                                <input type="time" id="auto-end" class="form-control form-control-lg" value="18:00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Interval (minutes)</label>
                                <select id="auto-interval" class="form-select form-control-lg">
                                    <option value="15">15 min</option>
                                    <option value="30" selected>30 min</option>
                                    <option value="45">45 min</option>
                                    <option value="60">60 min</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-outline-primary w-100" id="auto-generate" style="height: 48px;">
                                    <i class="fas fa-cog me-2"></i>Generate
                                </button>
                            </div>
                        </div>

                        <!-- Break Time Selector -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Break Start</label>
                                <input type="time" id="break-start" class="form-control form-control-lg" value="12:00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Break Duration</label>
                                <select id="break-duration" class="form-select form-control-lg">
                                    <option value="0">No Break</option>
                                    <option value="15">15 min</option>
                                    <option value="30" selected>30 min</option>
                                    <option value="45">45 min</option>
                                    <option value="60">60 min</option>
                                    <option value="90">1 hr 30 min</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <small class="text-muted">Slots overlapping the break window will be excluded.</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold mb-2 text-info">
                                <i class="fas fa-list-ul me-2"></i>Generated Time Slots (<span id="auto-slot-count">0</span>)
                            </h6>
                            <div class="bg-light p-3 rounded border" id="auto-slots" style="min-height: 60px;">
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-info-circle fa-2x mb-2"></i>
                                    <p><small>No slots generated yet. Use the form above to generate time slots.</small></p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info d-flex align-items-center">
                            <i class="fas fa-lightbulb me-3 fa-2x"></i>
                            <div>
                                <strong>Tip:</strong> Automated mode will generate all time slots between the start and end time based on the selected interval.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between mt-4 pt-4 border-top">
                    <button id="clear-all-slots" class="btn btn-outline-danger">
                        <i class="fas fa-trash me-2"></i>Clear All Times
                    </button>
                    <button id="save-availability" class="btn btn-success btn-lg px-5">
                        <i class="fas fa-save me-2"></i>Save My Schedule
                    </button>
                </div>
                <div id="save-status" class="mt-3"></div>
            </div>
        </div>

    <?php else: ?>
        <!-- Owner Schedule Overview -->
        <div class="card">
            <div class="card-header">
                <strong>Shop: <?= esc($shop['shop_name']) ?></strong>
                <small class="text-muted">All barber schedules overview</small>
            </div>
            <div class="card-body">
                <?php if (empty($all_schedules)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-times fa-2x mb-2"></i>
                        <p>No barbers assigned to your shop yet.</p>
                        <a href="<?= base_url('services/employees') ?>" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Manage Barbers
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($all_schedules as $employeeId => $data): ?>
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-user"></i>
                                <?= esc($data['employee']['first_name'] . ' ' . $data['employee']['last_name']) ?>
                                <small class="text-muted">(<?= esc($data['employee']['email']) ?>)</small>
                            </h5>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <?php foreach ($days as $day): ?>
                                                <th class="text-center"><?= ucfirst(substr($day, 0, 3)) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <?php foreach ($days as $day): ?>
                                                <?php $daySchedule = $data['schedule'][$day]; ?>
                                                <td class="text-center">
                                                    <?php if ($daySchedule['is_available']): ?>
                                                        <span class="badge bg-success mb-1">Available</span><br>
                                                        <small><?= $daySchedule['start_time'] ?> - <?= $daySchedule['end_time'] ?></small>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Off</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <hr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    <?php if ($user_role === 'barber'): ?>
        let addedSlots = []; // Store added time slots (start times) for manual mode
        let autoGeneratedSlots = []; // Store generated slots (start times) in automated mode
        let displayIntervalMinutes = 30; // Default display duration for manual slots
        let autoIntervalMinutes = 30; // Last used auto interval for display

        // Format time to 12-hour format for display
        function formatTime(timeString) {
            const [hours, minutes] = timeString.split(':');
            const h = parseInt(hours);
            const period = h >= 12 ? 'PM' : 'AM';
            const displayHour = h === 0 ? 12 : (h > 12 ? h - 12 : h);
            return `${displayHour}:${String(hours % 12 || 12).padStart(2, '0')}:${minutes} ${period}`;
        }

        // Format time to 12-hour format for display (simpler version)
        function formatTimeSimple(timeString) {
            const [hours, minutes] = timeString.split(':');
            const h = parseInt(hours);
            const period = h >= 12 ? 'PM' : 'AM';
            const displayHour = h === 0 ? 12 : (h > 12 ? h - 12 : h);
            return `${displayHour}:${minutes} ${period}`;
        }

        // Format a range like 08:00 - 08:30
        function formatRange(startHHMM, durationMinutes) {
            const start = new Date(`2000-01-01T${startHHMM}`);
            const end = new Date(start);
            end.setMinutes(end.getMinutes() + (durationMinutes || 30));
            const toLabel = (d) => {
                const h = d.getHours();
                const m = d.getMinutes();
                const period = h >= 12 ? 'PM' : 'AM';
                const displayHour = h === 0 ? 12 : (h > 12 ? h - 12 : h);
                return `${displayHour}:${String(m).padStart(2,'0')} ${period}`;
            };
            return `${toLabel(start)} - ${toLabel(end)}`;
        }

        // Add a time slot (manual mode)
        function addTimeSlot(time) {
            console.log('Adding time slot:', time);

            // Check if slot already exists
            if (addedSlots.includes(time)) {
                alert('This time slot is already added!');
                return;
            }

            addedSlots.push(time);
            console.log('Current slots:', addedSlots);
            renderSlots();

            // Update slot count
            document.getElementById('slot-count').textContent = addedSlots.length;

            // Show success feedback
            const addBtn = document.getElementById('add-manual-time');
            const originalHTML = addBtn.innerHTML;
            addBtn.innerHTML = '<i class="fas fa-check"></i> Added!';
            addBtn.classList.remove('btn-primary');
            addBtn.classList.add('btn-success');

            setTimeout(() => {
                addBtn.innerHTML = originalHTML;
                addBtn.classList.remove('btn-success');
                addBtn.classList.add('btn-primary');
            }, 1000);
        }

        // Remove a time slot (manual mode)
        function removeTimeSlot(time) {
            addedSlots = addedSlots.filter(slot => slot !== time);
            renderSlots();
            document.getElementById('slot-count').textContent = addedSlots.length;
        }

        // Render all time slots (manual mode)
        function renderSlots() {
            const container = document.getElementById('time-slots');
            const noSlotsMsg = document.getElementById('no-slots-message');

            if (addedSlots.length === 0) {
                if (noSlotsMsg) noSlotsMsg.style.display = 'block';
                container.innerHTML = '';
                return;
            }

            if (noSlotsMsg) noSlotsMsg.style.display = 'none';
            container.innerHTML = '';

            // Sort slots by time
            const sortedSlots = addedSlots.sort();

            sortedSlots.forEach(time => {
                const slotDiv = document.createElement('div');
                slotDiv.className = 'badge bg-dark px-3 py-2 mb-2 me-2';
                slotDiv.style.display = 'inline-flex';
                slotDiv.style.alignItems = 'center';
                slotDiv.style.gap = '0.5rem';

                const timeText = document.createElement('span');
                timeText.textContent = formatRange(time, displayIntervalMinutes);

                const removeBtn = document.createElement('button');
                removeBtn.className = 'btn btn-sm btn-danger ms-2';
                removeBtn.style.width = '24px';
                removeBtn.style.height = '24px';
                removeBtn.style.padding = '0';
                removeBtn.style.display = 'flex';
                removeBtn.style.alignItems = 'center';
                removeBtn.style.justifyContent = 'center';
                removeBtn.innerHTML = '×';
                removeBtn.onclick = function() {
                    removeTimeSlot(time);
                };

                slotDiv.appendChild(timeText);
                slotDiv.appendChild(removeBtn);
                container.appendChild(slotDiv);
            });
        }

        // Quick add time from preset buttons
        function quickAddTime(time) {
            console.log('Quick add time:', time);
            addTimeSlot(time);
        }

        // Generate slots for manual mode by fixed interval respecting current working window
        function generateIntervalSlots(intervalMinutes) {
            const start = document.getElementById('interval-start').value || '08:00';
            const end = document.getElementById('interval-end').value || '18:00';
            displayIntervalMinutes = intervalMinutes;
            const startDate = new Date(`2000-01-01T${start}`);
            const endDate = new Date(`2000-01-01T${end}`);
            if (!(startDate < endDate)) {
                alert('Start time must be before end time.');
                return;
            }
            const slots = [];
            const cursor = new Date(startDate);
            while (cursor < endDate) {
                const t = cursor.toTimeString().slice(0, 5);
                slots.push(t);
                cursor.setMinutes(cursor.getMinutes() + intervalMinutes);
            }
            // Merge into manual slots, de-duplicated
            const set = new Set([...(addedSlots || []), ...slots]);
            addedSlots = Array.from(set).sort();
            renderSlots();
            document.getElementById('slot-count').textContent = addedSlots.length;
        }

        // Generate auto slots
        function generateAutoSlots() {
            const start = document.getElementById('auto-start').value;
            const end = document.getElementById('auto-end').value;
            const interval = parseInt(document.getElementById('auto-interval').value);
            autoIntervalMinutes = interval;
            const breakStartVal = document.getElementById('break-start').value;
            const breakDur = parseInt(document.getElementById('break-duration').value || '0');

            if (!start || !end || isNaN(interval)) {
                alert('Please fill all fields correctly.');
                return;
            }

            const slots = [];
            const startTime = new Date(`2000-01-01T${start}`);
            const endTime = new Date(`2000-01-01T${end}`);
            let current = new Date(startTime);

            // Compute break window, if any
            let breakStart = null;
            let breakEnd = null;
            if (breakDur > 0 && breakStartVal) {
                breakStart = new Date(`2000-01-01T${breakStartVal}`);
                breakEnd = new Date(breakStart);
                breakEnd.setMinutes(breakEnd.getMinutes() + breakDur);
            }

            while (current < endTime) {
                const timeStr = current.toTimeString().slice(0, 5);
                // Exclude slot if it overlaps the break window
                const slotStart = new Date(current);
                const slotEnd = new Date(current);
                slotEnd.setMinutes(slotEnd.getMinutes() + interval);
                const overlapsBreak = breakStart && breakEnd && !(slotEnd <= breakStart || slotStart >= breakEnd);
                if (!overlapsBreak) {
                    slots.push(timeStr);
                }
                current.setMinutes(current.getMinutes() + interval);
            }

            autoGeneratedSlots = slots;
            renderAutoSlots();
            document.getElementById('auto-slot-count').textContent = slots.length;
        }

        // Render auto generated slots
        function renderAutoSlots() {
            const container = document.getElementById('auto-slots');

            if (autoGeneratedSlots.length === 0) {
                container.innerHTML = `
            <div class="text-center text-muted py-3">
                <i class="fas fa-info-circle fa-2x mb-2"></i>
                <p><small>No slots generated yet. Use the form above to generate time slots.</small></p>
            </div>
        `;
                return;
            }

            container.innerHTML = '';

            autoGeneratedSlots.forEach(time => {
                const slotDiv = document.createElement('div');
                slotDiv.className = 'badge bg-primary px-3 py-2 mb-2 me-2';
                slotDiv.style.display = 'inline-flex';
                slotDiv.style.alignItems = 'center';

                const timeText = document.createElement('span');
                timeText.textContent = formatRange(time, autoIntervalMinutes);

                slotDiv.appendChild(timeText);
                container.appendChild(slotDiv);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize slots arrays
            addedSlots = [];
            autoGeneratedSlots = [];
            renderSlots();
            renderAutoSlots();

            // Load selected day/week slots immediately
            document.getElementById('slots-title-date').textContent = new Date(document.getElementById('schedule-date').value || new Date().toISOString().split('T')[0]).toLocaleDateString();
            loadTodayTimeslots();

            let currentTodaySlots = [];
            let isEditingToday = false;

            // Time slot configuration for today's editor
            const slotStart = 8 * 60; // 8:00 AM in minutes
            const slotEnd = 19 * 60; // 7:00 PM in minutes
            const slotDuration = 30; // 30 minutes

            function pad(n) {
                return n < 10 ? '0' + n : n;
            }

            // Auto-generate button handler
            document.getElementById('auto-generate').onclick = function() {
                generateAutoSlots();
            };

            // Manual time add button handler
            document.getElementById('add-manual-time').onclick = function() {
                const timeInput = document.getElementById('manual-time');
                const time = timeInput.value;
                if (time) {
                    addTimeSlot(time);
                    timeInput.value = ''; // Clear after adding
                } else {
                    alert('Please select a time first!');
                }
            };

            // Enter key handler for manual time input
            document.getElementById('manual-time').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const time = this.value;
                    if (time) {
                        addTimeSlot(time);
                        this.value = '';
                    }
                }
            });

            function loadTodayTimeslots() {
                const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD format
                fetch('<?= base_url('schedule/barber-availability') ?>?date=' + encodeURIComponent(today))
                    .then(res => {
                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        return res.json();
                    })
                    .then(data => {
                        const slots = data.slots || [];
                        currentTodaySlots = [...slots];
                        const container = document.getElementById('today-timeslots');
                        const editBtn = document.getElementById('edit-today-slots');

                        if (slots.length === 0) {
                            container.innerHTML = `
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <p>No available timeslots set for today</p>
                            <small class="text-muted">Set your availability below or click "Edit Today's Slots" to add some</small>
                        </div>
                    `;
                            editBtn.style.display = 'inline-block';
                        } else {
                            // Build a vertical timeline layout with duration display
                            const toMinutes = (hhmm) => {
                                const [h, m] = hhmm.split(':').map(Number);
                                return h * 60 + m;
                            };
                            let html = '<div class="timeline"><ul class="timeline-list">';
                            slots.forEach((slot, idx) => {
                                const current = toMinutes(slot);
                                const next = idx < slots.length - 1 ? toMinutes(slots[idx + 1]) : current + 30;
                                const duration = Math.max(15, next - current); // assume at least 15 mins
                                html += `
                                    <li class=\"timeline-item\">
                                        <span class=\"timeline-dot\"></span>
                                        <div class=\"timeline-content\">
                                            <div class=\"timeline-time\">${formatRange(slot, duration)}</div>
                                        </div>
                                    </li>`;
                            });
                            html += '</ul></div>';
                            html += '<div class="text-center mt-2"><small class="text-muted">Available for booking</small></div>';
                            container.innerHTML = html;
                            editBtn.style.display = 'inline-block';
                        }
                    })
                    .catch(error => {
                        document.getElementById('today-timeslots').innerHTML = `
                    <div class="text-center text-danger py-4">
                        <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                        <p>Error loading timeslots</p>
                        <small>${error.message}</small>
                    </div>
                `;
                    });
            }

            function showTodaySlotsEditor() {
                isEditingToday = true;
                const container = document.getElementById('today-timeslots');
                const editBtn = document.getElementById('edit-today-slots');
                const saveBtn = document.getElementById('save-today-slots');
                const cancelBtn = document.getElementById('cancel-today-edit');

                // Hide edit button, show save/cancel
                editBtn.style.display = 'none';
                saveBtn.style.display = 'inline-block';
                cancelBtn.style.display = 'inline-block';

                // Create editable time slots
                let html = '<div class="mb-3"><small class="text-muted">Click time slots to toggle availability:</small></div>';
                html += '<div class="d-flex flex-wrap gap-2 justify-content-center">';

                // Generate all possible time slots (8:00 to 19:00, 30-min intervals)
                for (let mins = slotStart; mins <= slotEnd; mins += slotDuration) {
                    const h = Math.floor(mins / 60);
                    const m = mins % 60;
                    const time = pad(h) + ':' + pad(m);
                    const isActive = currentTodaySlots.includes(time);
                    html += `<button class="btn btn-sm ${isActive ? 'btn-success' : 'btn-outline-secondary'} today-slot-edit" data-time="${time}">${time}</button>`;
                }
                html += '</div>';
                container.innerHTML = html;

                // Add click handlers
                document.querySelectorAll('.today-slot-edit').forEach(btn => {
                    btn.onclick = function() {
                        const time = this.dataset.time;
                        if (this.classList.contains('btn-success')) {
                            this.classList.remove('btn-success');
                            this.classList.add('btn-outline-secondary');
                            currentTodaySlots = currentTodaySlots.filter(slot => slot !== time);
                        } else {
                            this.classList.remove('btn-outline-secondary');
                            this.classList.add('btn-success');
                            currentTodaySlots.push(time);
                        }
                    };
                });
            }

            function saveTodaySlots() {
                const today = new Date().toISOString().split('T')[0];

                fetch('<?= base_url('barber/update-availability') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                        },
                        body: JSON.stringify({
                            date: today,
                            slots: currentTodaySlots
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Exit edit mode
                            exitTodayEditMode();
                            // Reload the display
                            loadTodayTimeslots();
                            // Show success message
                            const container = document.getElementById('today-timeslots');
                            const successMsg = document.createElement('div');
                            successMsg.className = 'alert alert-success mt-3';
                            successMsg.innerHTML = '<i class="fas fa-check-circle"></i> Today\'s schedule updated successfully!';
                            container.appendChild(successMsg);
                            setTimeout(() => successMsg.remove(), 3000);
                        } else {
                            alert('Error updating schedule: ' + (data.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        alert('Error saving changes: ' + error.message);
                    });
            }

            function exitTodayEditMode() {
                isEditingToday = false;
                const editBtn = document.getElementById('edit-today-slots');
                const saveBtn = document.getElementById('save-today-slots');
                const cancelBtn = document.getElementById('cancel-today-edit');

                editBtn.style.display = 'inline-block';
                saveBtn.style.display = 'none';
                cancelBtn.style.display = 'none';
            }

            // Add event handlers for today's slots CRUD
            document.getElementById('edit-today-slots').onclick = function() {
                showTodaySlotsEditor();
            };

            document.getElementById('save-today-slots').onclick = function() {
                saveTodaySlots();
            };

            document.getElementById('cancel-today-edit').onclick = function() {
                exitTodayEditMode();
                loadTodayTimeslots(); // Reload original state
            };
            // Date change handler (also refresh top view)
            document.getElementById('schedule-date').addEventListener('change', function() {
                const selectedDate = this.value;
                if (!selectedDate) return;

                // Load existing slots for this date
                fetch('<?= base_url('schedule/barber-availability') ?>?date=' + encodeURIComponent(selectedDate))
                    .then(res => res.json())
                    .then(data => {
                        const slots = data.slots || [];
                        addedSlots = slots;
                        renderSlots();
                        document.getElementById('slot-count').textContent = slots.length;
                    })
                    .catch(error => {
                        console.error('Error loading availability:', error);
                        addedSlots = [];
                        renderSlots();
                        document.getElementById('slot-count').textContent = '0';
                    });
                // refresh top summary
                document.getElementById('slots-title-date').textContent = new Date(selectedDate).toLocaleDateString();
                loadTodayTimeslots();
            });

            // Display filter handlers
            document.getElementById('display-mode').addEventListener('change', loadTodayTimeslots);
            document.getElementById('display-interval').addEventListener('change', loadTodayTimeslots);

            // Save availability handler
            document.getElementById('save-availability').addEventListener('click', function() {
                const selectedDate = document.getElementById('schedule-date').value;

                // Determine which mode is active and get the appropriate slots
                const manualTab = document.getElementById('manual-tab');
                const isManualActive = manualTab.classList.contains('active');

                let selectedSlots = [];
                if (isManualActive) {
                    selectedSlots = addedSlots; // Use manual mode slots
                } else {
                    selectedSlots = autoGeneratedSlots; // Use automated mode slots
                }

                if (selectedSlots.length === 0) {
                    alert('Please add at least one time slot before saving!');
                    return;
                }

                const saveBtn = this;
                const originalText = saveBtn.innerHTML;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                saveBtn.disabled = true;

                fetch('<?= base_url('barber/update-availability') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                        },
                        body: JSON.stringify({
                            date: selectedDate,
                            slots: selectedSlots
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        const statusDiv = document.getElementById('save-status');
                        if (data.success) {
                            statusDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Schedule saved successfully!</div>';
                            // Reload today's slots if we're editing today
                            if (selectedDate === new Date().toISOString().split('T')[0]) {
                                loadTodayTimeslots();
                            }
                        } else {
                            statusDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Error: ' + (data.error || 'Failed to save') + '</div>';
                        }
                        setTimeout(() => {
                            statusDiv.innerHTML = '';
                        }, 3000);
                    })
                    .catch(error => {
                        document.getElementById('save-status').innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i>Error: ' + error.message + '</div>';
                    })
                    .finally(() => {
                        saveBtn.innerHTML = originalText;
                        saveBtn.disabled = false;
                    });
            });

            // Clear all slots handler
            document.getElementById('clear-all-slots').onclick = function() {
                const manualTab = document.getElementById('manual-tab');
                const isManualActive = manualTab.classList.contains('active');

                if (isManualActive) {
                    if (confirm('Clear all manually added time slots?')) {
                        addedSlots = [];
                        renderSlots();
                        document.getElementById('slot-count').textContent = '0';
                    }
                } else {
                    if (confirm('Clear all auto-generated time slots?')) {
                        autoGeneratedSlots = [];
                        renderAutoSlots();
                        document.getElementById('auto-slot-count').textContent = '0';
                    }
                }
            };
        });
    <?php endif; ?>
</script>

<style>
    /* Modern Card Styling */
    .card.shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .card.shadow-lg {
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
        border: none;
    }

    .card-header.bg-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    /* Timeslot chip */
    .timeslot-chip {
        background: #e8f5e9;
        color: #1e7e34;
        border: 1px solid #c3e6cb;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .timeslot-col .timeslot-chip {
        width: 100%;
        justify-content: flex-start;
    }

    /* Timeline layout */
    .timeline {
        position: relative;
        padding-left: 1.5rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 12px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 0.75rem;
    }

    .timeline-dot {
        position: absolute;
        left: 4px;
        top: 6px;
        width: 16px;
        height: 16px;
        background: #28a745;
        border: 2px solid #fff;
        border-radius: 50%;
        box-shadow: 0 0 0 2px #28a74522;
    }

    .timeline-content {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
    }

    .timeline-time {
        font-weight: 600;
        color: #343a40;
    }

    /* Time Slot Badges */
    #time-slots .badge,
    #auto-slots .badge {
        font-size: 0.95rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        margin: 0.25rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    #time-slots .badge:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    #auto-slots .badge {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    /* Badge styles with gradients */
    .badge.bg-dark {
        background: linear-gradient(135deg, #495057 0%, #343a40 100%);
    }

    .badge.bg-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    .badge.bg-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    }

    /* Remove button on badges */
    .badge .btn-sm {
        font-size: 0.75rem;
        line-height: 1;
        padding: 0.125rem 0.375rem;
    }

    /* No slots message */
    #no-slots-message {
        padding: 2rem;
        color: #6c757d;
    }

    /* Save status styling */
    #save-status .alert {
        margin-bottom: 0;
    }

    /* Tab Navigation */
    .nav-tabs {
        border-bottom: 2px solid #dee2e6;
    }

    .nav-tabs .nav-link {
        color: #6c757d;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        color: #007bff;
        border-color: transparent;
    }

    .nav-tabs .nav-link.active {
        color: #007bff;
        background-color: #f8f9fa;
        border-color: #dee2e6 #dee2e6 transparent;
    }

    /* Form inputs */
    .form-control-lg {
        border-radius: 0.5rem;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
    }

    .form-control-lg:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
    }

    /* Quick time buttons */
    .btn-outline-primary {
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
    }

    /* Today's slots editing */
    .today-slot-edit {
        transition: all 0.2s ease;
        margin: 2px;
    }

    .today-slot-edit:hover {
        transform: scale(1.05);
    }

    /* Info alert */
    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        border: 1px solid #bee5eb;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .nav-tabs {
            font-size: 0.9rem;
        }

        .btn-group {
            flex-wrap: wrap;
        }

        .quick-time-buttons {
            font-size: 0.8rem;
        }
    }

    /* Loading spinner */
    .fa-spinner {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Action buttons */
    .btn-lg {
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-lg:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    /* Table styles */
    .table th,
    .table td {
        vertical-align: middle;
    }

    .table-responsive {
        border-radius: 0.5rem;
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.9em;
        }
    }
</style>

<?= $this->endSection() ?>