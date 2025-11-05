<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mt-4">
                <i class="fas fa-calendar-plus text-primary"></i> Book Your Appointment
            </h1>
            <ol class="breadcrumb mb-4">
                <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Book Appointment</li>
            </ol>
        </div>
        <div>
            <a href="/booking" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Full View
            </a>
        </div>
    </div>

    <!-- Quick Booking Steps -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="step-item">
                                <i class="fas fa-store fa-2x text-primary mb-2"></i>
                                <h6>1. Choose Shop</h6>
                                <small class="text-muted">Select your preferred barbershop</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="step-item">
                                <i class="fas fa-user-tie fa-2x text-success mb-2"></i>
                                <h6>2. Pick Barber</h6>
                                <small class="text-muted">Choose your barber</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="step-item">
                                <i class="fas fa-scissors fa-2x text-warning mb-2"></i>
                                <h6>3. Select Service</h6>
                                <small class="text-muted">Pick your service</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="step-item">
                                <i class="fas fa-calendar-check fa-2x text-info mb-2"></i>
                                <h6>4. Book Time</h6>
                                <small class="text-muted">Choose date & time</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Booking Form -->
        <div class="col-lg-8">
            <form id="clientBookingForm">
                <!-- Step 1: Shop Selection -->
                <div class="card mb-4" id="step1">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-store"></i> Step 1: Choose Your Barbershop
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="shops-list">
                            <!-- Shops will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Step 2: Barber Selection -->
                <div class="card mb-4" id="step2" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-tie"></i> Step 2: Choose Your Barber
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="barbers-list">
                            <!-- Barbers will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Step 3: Service Selection -->
                <div class="card mb-4" id="step3" style="display: none;">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-scissors"></i> Step 3: Select Your Service
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row" id="services-list">
                            <!-- Services will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Step 4: Date & Time Selection -->
                <div class="card mb-4" id="step4" style="display: none;">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-check"></i> Step 4: Choose Date & Time
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="appointment_date" class="form-label">Select Date</label>
                                <input type="date" class="form-control" id="appointment_date" 
                                       min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="appointment_time" class="form-label">Select Time</label>
                                <select class="form-select" id="appointment_time" required>
                                    <option value="">Choose a time...</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Special Instructions -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Special Instructions (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" 
                                      placeholder="Any special requests or notes..."></textarea>
                        </div>

                        <!-- Appointment Notes -->
                        <div class="mb-3">
                            <label class="form-label">Appointment Details</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-select mb-2" id="haircut_type" name="haircut_type">
                                        <option value="">Haircut Type</option>
                                        <option value="fade">Fade</option>
                                        <option value="undercut">Undercut</option>
                                        <option value="pompadour">Pompadour</option>
                                        <option value="buzz_cut">Buzz Cut</option>
                                        <option value="long_hair">Long Hair</option>
                                        <option value="beard_trim">Beard Trim</option>
                                        <option value="full_service">Full Service</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select mb-2" id="urgency" name="urgency">
                                        <option value="normal">Normal Priority</option>
                                        <option value="urgent">Urgent</option>
                                        <option value="asap">ASAP</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Summary -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Booking Summary</h6>
                                <div id="booking-summary">
                                    <p class="text-muted">Complete the steps above to see your booking summary</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Confirm Booking
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Tips -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb"></i> Booking Tips
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Book at least 24 hours in advance
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Arrive 5 minutes early
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Cancel 24 hours before if needed
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success"></i>
                            Bring a photo for reference
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Your Appointments -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> Your Appointments
                    </h6>
                </div>
                <div class="card-body">
                    <div id="client-appointments">
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <p class="mb-0">No appointments yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Confirmation Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle"></i> Booking Confirmation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="booking-details">
                    <!-- Booking details will be populated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="confirm-booking">
                    <i class="fas fa-check"></i> Confirm Booking
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let selectedShop = null;
let selectedBarber = null;
let selectedService = null;
let availableSlots = [];

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadShops();
    loadClientAppointments();
});

// Load shops
function loadShops() {
    fetch('/CleanCut/booking/shops')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayShops(data.shops);
        }
    })
    .catch(error => console.error('Error loading shops:', error));
}

// Display shops
function displayShops(shops) {
    const container = document.getElementById('shops-list');
    
    if (shops.length === 0) {
        container.innerHTML = '<div class="col-12 text-center py-4"><p class="text-muted">No shops available</p></div>';
        return;
    }
    
    let html = '';
    shops.forEach(shop => {
        html += `
            <div class="col-md-6 mb-3">
                <div class="card shop-card h-100" onclick="selectShop(${shop.shop_id}, '${shop.shop_name}')">
                    <div class="card-body text-center">
                        <i class="fas fa-cut fa-3x text-primary mb-3"></i>
                        <h6 class="card-title">${shop.shop_name}</h6>
                        <p class="card-text text-muted">${shop.address}</p>
                        <button class="btn btn-primary btn-sm">Select Shop</button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Select shop
function selectShop(shopId, shopName) {
    selectedShop = { id: shopId, name: shopName };
    
    // Update UI
    document.querySelectorAll('.shop-card').forEach(card => {
        card.classList.remove('border-primary');
    });
    event.currentTarget.classList.add('border-primary');
    
    // Load barbers for this shop
    loadBarbers(shopId);
    
    // Show next step
    document.getElementById('step2').style.display = 'block';
    document.getElementById('step2').scrollIntoView({ behavior: 'smooth' });
}

// Load barbers for shop
function loadBarbers(shopId) {
    fetch(`/CleanCut/booking/shop-barbers/${shopId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayBarbers(data.barbers);
        }
    })
    .catch(error => console.error('Error loading barbers:', error));
}

// Display barbers
function displayBarbers(barbers) {
    const container = document.getElementById('barbers-list');
    
    if (barbers.length === 0) {
        container.innerHTML = '<div class="col-12 text-center py-4"><p class="text-muted">No barbers available</p></div>';
        return;
    }
    
    let html = '';
    barbers.forEach(barber => {
        html += `
            <div class="col-md-6 mb-3">
                <div class="card barber-card h-100" onclick="selectBarber(${barber.user_id}, '${barber.first_name} ${barber.last_name}')">
                    <div class="card-body text-center">
                        <i class="fas fa-user-tie fa-3x text-success mb-3"></i>
                        <h6 class="card-title">${barber.first_name} ${barber.last_name}</h6>
                        <button class="btn btn-success btn-sm">Select Barber</button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Select barber
function selectBarber(barberId, barberName) {
    selectedBarber = { id: barberId, name: barberName };
    
    // Update UI
    document.querySelectorAll('.barber-card').forEach(card => {
        card.classList.remove('border-success');
    });
    event.currentTarget.classList.add('border-success');
    
    // Load services for this shop
    loadServices(selectedShop.id);
    
    // Show next step
    document.getElementById('step3').style.display = 'block';
    document.getElementById('step3').scrollIntoView({ behavior: 'smooth' });
}

// Load services for shop
function loadServices(shopId) {
    fetch(`/CleanCut/booking/shop-services/${shopId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayServices(data.services);
        }
    })
    .catch(error => console.error('Error loading services:', error));
}

// Display services
function displayServices(services) {
    const container = document.getElementById('services-list');
    
    if (services.length === 0) {
        container.innerHTML = '<div class="col-12 text-center py-4"><p class="text-muted">No services available</p></div>';
        return;
    }
    
    let html = '';
    services.forEach(service => {
        html += `
            <div class="col-md-6 mb-3">
                <div class="card service-card h-100" onclick="selectService(${service.service_id}, '${service.service_name}', ${service.price}, ${service.duration})">
                    <div class="card-body text-center">
                        <i class="fas fa-scissors fa-3x text-warning mb-3"></i>
                        <h6 class="card-title">${service.service_name}</h6>
                        <p class="card-text text-muted">₱${service.price.toLocaleString()}</p>
                        <small class="text-muted">${service.duration} minutes</small>
                        <br>
                        <button class="btn btn-warning btn-sm mt-2">Select Service</button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Select service
function selectService(serviceId, serviceName, price, duration) {
    selectedService = { id: serviceId, name: serviceName, price: price, duration: duration };
    
    // Update UI
    document.querySelectorAll('.service-card').forEach(card => {
        card.classList.remove('border-warning');
    });
    event.currentTarget.classList.add('border-warning');
    
    // Show next step
    document.getElementById('step4').style.display = 'block';
    document.getElementById('step4').scrollIntoView({ behavior: 'smooth' });
    
    // Update booking summary
    updateBookingSummary();
}

// Update booking summary
function updateBookingSummary() {
    const summary = document.getElementById('booking-summary');
    
    if (selectedShop && selectedBarber && selectedService) {
        summary.innerHTML = `
            <div class="row">
                <div class="col-6"><strong>Shop:</strong></div>
                <div class="col-6">${selectedShop.name}</div>
            </div>
            <div class="row">
                <div class="col-6"><strong>Barber:</strong></div>
                <div class="col-6">${selectedBarber.name}</div>
            </div>
            <div class="row">
                <div class="col-6"><strong>Service:</strong></div>
                <div class="col-6">${selectedService.name}</div>
            </div>
            <div class="row">
                <div class="col-6"><strong>Price:</strong></div>
                <div class="col-6">₱${selectedService.price.toLocaleString()}</div>
            </div>
            <div class="row">
                <div class="col-6"><strong>Duration:</strong></div>
                <div class="col-6">${selectedService.duration} minutes</div>
            </div>
        `;
    }
}

// Get available time slots when date is selected
document.getElementById('appointment_date').addEventListener('change', function() {
    const date = this.value;
    const barberId = selectedBarber ? selectedBarber.id : null;
    const serviceId = selectedService ? selectedService.id : null;
    
    if (date && barberId && serviceId) {
        getAvailableSlots(barberId, date, serviceId);
    }
});

// Get available time slots
function getAvailableSlots(barberId, date, serviceId) {
    fetch('/CleanCut/booking/available-slots', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            barber_id: barberId,
            date: date,
            service_id: serviceId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayTimeSlots(data.slots);
        } else {
            document.getElementById('appointment_time').innerHTML = '<option value="">No available times</option>';
        }
    })
    .catch(error => {
        console.error('Error loading time slots:', error);
        document.getElementById('appointment_time').innerHTML = '<option value="">Error loading times</option>';
    });
}

// Display time slots
function displayTimeSlots(slots) {
    const select = document.getElementById('appointment_time');
    select.innerHTML = '<option value="">Choose a time...</option>';
    
    if (slots.length === 0) {
        select.innerHTML = '<option value="">No available times</option>';
        return;
    }
    
    slots.forEach(slot => {
        const time = new Date('2000-01-01 ' + slot);
        const timeString = time.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });
        select.innerHTML += `<option value="${slot}">${timeString}</option>`;
    });
}

// Handle form submission
document.getElementById('clientBookingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!selectedShop || !selectedBarber || !selectedService) {
        alert('Please complete all steps before booking');
        return;
    }
    
    const formData = new FormData();
    formData.append('barber_id', selectedBarber.id);
    formData.append('service_id', selectedService.id);
    formData.append('appointment_date', document.getElementById('appointment_date').value);
    formData.append('appointment_time', document.getElementById('appointment_time').value);
    formData.append('notes', document.getElementById('notes').value);
    formData.append('haircut_type', document.getElementById('haircut_type').value);
    formData.append('urgency', document.getElementById('urgency').value);
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Booking...';
    submitBtn.disabled = true;
    
    fetch('/CleanCut/booking/book', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showBookingConfirmation(data);
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while booking');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Show booking confirmation
function showBookingConfirmation(data) {
    const needsPay = !!data.requires_payment;
    const feeAmt = Number(data.booking_fee_amount || 0);
    const pct = Number(data.booking_fee_percentage || 0);
    const details = `
        <div class="text-center mb-4">
            <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
            <h4 class="text-success">Booking Created</h4>
            ${needsPay ? '<p class="mb-0">A booking fee is required to confirm.</p>' : '<p class="mb-0">No booking fee required.</p>'}
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card bg-light"><div class="card-body">
                    <h6 class="card-title">Appointment ID</h6>
                    <p class="card-text fw-bold">#${data.appointment_id}</p>
                </div></div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light"><div class="card-body">
                    <h6 class="card-title">Service</h6>
                    <p class="card-text fw-bold">${selectedService.name}</p>
                </div></div>
            </div>
            ${needsPay ? `
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Booking Fee</h6>
                            <small class="text-muted">${pct.toFixed(2)}% of service price</small>
                        </div>
                        <div class="fs-5 fw-bold">₱${feeAmt.toFixed(2)}</div>
                    </div>
                </div>
            </div>` : ''}
        </div>
        ${needsPay ? `<div class="text-end mt-3 text-muted">Proceed with payment to confirm.</div>` : ''}
    `;

    document.getElementById('booking-details').innerHTML = details;
    document.getElementById('bookingModal').modal('show');

    const confirmBtn = document.getElementById('confirm-booking');
    confirmBtn.textContent = needsPay ? 'Proceed to Payment' : 'Confirm Booking';
    confirmBtn.onclick = function() {
        if (needsPay) {
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            fetch('/CleanCut/payments/checkout/' + data.appointment_id, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(res => {
                if (res && res.checkout_url) {
                    window.location.href = res.checkout_url;
                } else {
                    alert('Payment error: ' + (res.error || 'Unable to start checkout'));
                }
            })
            .catch(() => alert('Payment error'));
        } else {
            document.getElementById('bookingModal').modal('hide');
            alert('Booking confirmed successfully!');
            location.reload();
        }
    };
}

// Load client appointments
function loadClientAppointments() {
    fetch('/CleanCut/booking/my-appointments')
    .then(response => response.json())
    .then(data => {
        if (data.success && data.appointments.length > 0) {
            displayClientAppointments(data.appointments);
        }
    })
    .catch(error => console.error('Error loading appointments:', error));
}

// Display client appointments
function displayClientAppointments(appointments) {
    const container = document.getElementById('client-appointments');
    
    let html = '';
    appointments.slice(0, 3).forEach(appointment => {
        const date = new Date(appointment.appointment_date);
        const formattedDate = date.toLocaleDateString();
        const statusClass = 'status-' + appointment.status;
        
        html += `
            <div class="d-flex justify-content-between align-items-center mb-3 p-2 border rounded bg-light">
                <div>
                    <strong class="text-dark">${formattedDate}</strong><br>
                    <small class="text-muted">${appointment.appointment_time}</small>
                </div>
                <span class="badge ${statusClass}">${appointment.status}</span>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Confirm booking
// Note: click handler is dynamically overwritten in showBookingConfirmation
</script>

<style>
.shop-card, .barber-card, .service-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.shop-card:hover, .barber-card:hover, .service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.step-item {
    padding: 1rem;
}

.step-item i {
    display: block;
}

.border-primary {
    border: 2px solid #007bff !important;
}

.border-success {
    border: 2px solid #28a745 !important;
}

.border-warning {
    border: 2px solid #ffc107 !important;
}

.status-pending {
    background-color: #ffc107;
}

.status-confirmed {
    background-color: #17a2b8;
}

.status-completed {
    background-color: #28a745;
}

.status-cancelled {
    background-color: #dc3545;
}
</style>
<?= $this->endSection() ?>
