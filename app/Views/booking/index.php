<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-calendar-plus text-primary"></i> Book Appointment
                </h1>
                <div class="text-muted">
                    <i class="fas fa-info-circle"></i> Select a shop to start booking
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Booking Area -->
        <div class="col-lg-8">
            <!-- Step 1: Shop Selection -->
            <div class="card shadow-sm mb-4" id="shop-selection">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-store"></i> Step 1: Choose Your Barbershop
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($shops)): ?>
                        <div class="row">
                            <?php foreach ($shops as $shop): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card shop-card h-100 border-0 shadow-sm hover-lift" data-shop-id="<?= $shop['shop_id'] ?>">
                                        <div class="card-body text-center p-4">
                                            <div class="mb-3">
                                                <div class="shop-icon-wrapper">
                                                    <i class="fas fa-cut fa-3x text-primary"></i>
                                                </div>
                                            </div>
                                            <h6 class="card-title fw-bold text-dark mb-2"><?= $shop['shop_name'] ?></h6>
                                            <div class="shop-details mb-3">
                                                <p class="text-muted small mb-1">
                                                    <i class="fas fa-map-marker-alt text-danger"></i> <?= $shop['address'] ?>
                                                </p>
                                                <?php if ($shop['phone']): ?>
                                                    <p class="text-muted small mb-0">
                                                        <i class="fas fa-phone text-success"></i> <?= $shop['phone'] ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <button type="button" class="btn btn-primary btn-sm w-100 select-shop"
                                                data-shop-id="<?= $shop['shop_id'] ?>">
                                                <i class="fas fa-check me-1"></i> Select This Shop
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-store fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No Shops Available</h5>
                                <p class="text-muted mb-3">There are currently no barbershops registered in the system.</p>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Note:</strong> When shops register, they will automatically appear here for booking appointments.
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Step 2: Appointment Details (shown after shop selection) -->
            <div class="card shadow-sm" id="appointment-details" <?= (!empty($barbers) && !empty($services)) ? '' : 'style="display: none;"' ?>>
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-check"></i> Step 2: Book Your Appointment
                    </h5>
                </div>
                <div class="card-body">
                    <form id="bookingForm">
                        <!-- Selected Shop Info -->
                        <div class="alert alert-info mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Selected Shop:</strong> <span id="selected-shop-name" class="fw-bold"><?= $selectedShop ? htmlspecialchars($selectedShop['shop_name']) : '-' ?></span>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="changeShop()">
                                    <i class="fas fa-edit"></i> Change Shop
                                </button>
                            </div>
                        </div>

                        <!-- Barber and Service Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="barber_id" class="form-label fw-bold">
                                    <i class="fas fa-user-tie text-primary"></i> Select Barber
                                </label>
                                <select class="form-select form-select-lg" id="barber_id" name="barber_id" required>
                                    <option value="">Choose a barber...</option>
                                    <?php foreach ($barbers as $barber): ?>
                                        <option value="<?= $barber['user_id'] ?>">
                                            <?= $barber['first_name'] . ' ' . $barber['last_name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="service_id" class="form-label fw-bold">
                                    <i class="fas fa-scissors text-success"></i> Select Service
                                </label>
                                <select class="form-select form-select-lg" id="service_id" name="service_id" required>
                                    <option value="">Choose a service...</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?= $service['service_id'] ?>"
                                            data-price="<?= $service['price'] ?>"
                                            data-duration="<?= $service['duration'] ?>">
                                            <?= $service['service_name'] ?> - ₱<?= number_format($service['price'], 2) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Date and Time Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="appointment_date" class="form-label fw-bold">
                                    <i class="fas fa-calendar text-warning"></i> Select Date
                                </label>
                                <input type="date" class="form-control form-control-lg" id="appointment_date" name="appointment_date"
                                    min="<?= date('Y-m-d') ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="appointment_time" class="form-label fw-bold">
                                    <i class="fas fa-clock text-info"></i> Select Time
                                </label>
                                <select class="form-select form-select-lg" id="appointment_time" name="appointment_time" required>
                                    <option value="">Choose a time...</option>
                                </select>
                            </div>
                        </div>

                        <!-- Special Instructions -->
                        <div class="mb-4">
                            <label for="notes" class="form-label fw-bold">
                                <i class="fas fa-comment text-secondary"></i> Special Instructions (Optional)
                            </label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                placeholder="Any special instructions or preferences..."></textarea>
                        </div>

                        <!-- Appointment Notes -->
                        <div class="mb-4">
                            <label for="appointment_notes" class="form-label fw-bold">
                                <i class="fas fa-sticky-note text-info"></i> Appointment Notes (Optional)
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-select mb-2" id="haircut_type" name="haircut_type">
                                        <option value="">Select Haircut Type</option>
                                        <option value="fade">Fade</option>
                                        <option value="undercut">Undercut</option>
                                        <option value="pompadour">Pompadour</option>
                                        <option value="buzz_cut">Buzz Cut</option>
                                        <option value="long_hair">Long Hair</option>
                                        <option value="beard_trim">Beard Trim</option>
                                        <option value="mustache">Mustache</option>
                                        <option value="full_service">Full Service</option>
                                        <option value="touch_up">Touch Up</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <select class="form-select mb-2" id="urgency" name="urgency">
                                        <option value="normal">Normal</option>
                                        <option value="urgent">Urgent</option>
                                        <option value="asap">ASAP</option>
                                    </select>
                                </div>
                            </div>
                            <textarea class="form-control" id="appointment_notes" name="appointment_notes" rows="2"
                                placeholder="Additional notes about the appointment (haircut style, special requests, etc.)..."></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> These notes will help the barber prepare for your appointment
                            </div>
                        </div>

                        <!-- Service Details and Available Times -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold">
                                            <i class="fas fa-info-circle text-primary"></i> Service Details
                                        </h6>
                                        <p class="mb-1"><strong>Duration:</strong> <span id="service-duration" class="text-muted">-</span></p>
                                        <p class="mb-0"><strong>Price:</strong> <span id="service-price" class="text-success fw-bold">-</span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold">
                                            <i class="fas fa-clock text-warning"></i> Available Times
                                        </h6>
                                        <p class="mb-1"><strong>Shop Hours:</strong> 9:00 AM - 6:00 PM</p>
                                        <p class="mb-0"><strong>Time Slots:</strong> 30-minute intervals</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Book Appointment Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="fas fa-check me-2"></i> Book Appointment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="col-lg-4">
            <!-- Your Appointments -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> Your Appointments
                    </h5>
                </div>
                <div class="card-body">
                    <div id="appointments-list">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i>
                            <h6 class="text-muted">No upcoming appointments</h6>
                            <p class="small text-muted">Book your first appointment to see it here</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Information -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Booking Information
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">


                        <li class="mb-3 d-flex align-items-start">
                            <i class="fas fa-undo text-warning mt-1 me-3"></i>
                            <div>
                                <strong>Cancellation:</strong><br>
                                <small class="text-muted">Cancel up to 24 hours before</small>
                            </div>
                        </li>
                        <li class="d-flex align-items-start">
                            <i class="fas fa-phone text-info mt-1 me-3"></i>
                            <div>
                                <strong>Contact:</strong><br>
                                <small class="text-muted">Call for urgent changes</small>
                            </div>
                        </li>
                    </ul>
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
                    <i class="fas fa-check me-1"></i> Confirm Booking
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom CSS for better styling -->
<style>
    .shop-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .shop-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
    }

    .shop-card.selected {
        border: 2px solid #007bff !important;
        background-color: #f8f9ff;
    }

    .shop-icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #007bff, #0056b3);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        color: white;
    }

    .hover-lift:hover {
        transform: translateY(-3px);
    }

    .empty-state {
        padding: 2rem;
    }

    .form-select-lg,
    .form-control-lg {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }

    .card-header {
        border-bottom: none;
    }

    .alert {
        border: none;
        border-radius: 0.5rem;
    }

    .btn-lg {
        padding: 0.75rem 2rem;
        font-weight: 600;
    }

    .has-slot-feedback {
        border: 2px solid #2ecc40 !important;
        background: #d6ffe0 !important;
    }

    .no-slot-feedback {
        border: 2px solid #ff4136 !important;
        background: #ffe0e0 !important;
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link rel="stylesheet" href="<?= base_url('public/assets/css/flatpickr.min.css') ?>">
<script src="<?= base_url('public/assets/js/flatpickr.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        let selectedService = null;
        let availableSlots = [];
        let selectedShopId = <?= $selectedShop ? $selectedShop['shop_id'] : 'null' ?>;
        let selectedShopName = <?= $selectedShop ? "'" . htmlspecialchars($selectedShop['shop_name']) . "'" : 'null' ?>;

        // Initialize with pre-selected shop if available
        if (selectedShopId && selectedShopName) {
            // Mark the selected shop card
            $(`.shop-card[data-shop-id="${selectedShopId}"]`).addClass('selected');

            // Hide shop selection and show appointment details
            $('#shop-selection').hide();
            $('#appointment-details').show();
        }

        // Shop selection functionality with improved UI
        $('.select-shop').click(function() {
            const shopId = $(this).data('shop-id');
            const shopCard = $(this).closest('.shop-card');
            const shopName = shopCard.find('.card-title').text();

            selectedShopId = shopId;
            selectedShopName = shopName;

            // Update UI with smooth transitions
            $('.shop-card').removeClass('selected');
            shopCard.addClass('selected');

            // Show appointment details section with animation
            $('#shop-selection').fadeOut(300, function() {
                $('#appointment-details').fadeIn(300);
            });

            $('#selected-shop-name').text(shopName);

            // Load barbers and services for this shop
            loadShopData(shopId);
        });

        // Load barbers and services for selected shop
        function loadShopData(shopId) {
            // Show loading state
            $('#barber_id, #service_id').html('<option value="">Loading...</option>');

            // Load barbers with robust error handling and empty-state
            console.log('Loading barbers for shop:', shopId);
            $.get('<?= base_url('booking/shop-barbers') ?>/' + shopId)
                .done(function(data) {
                    console.log('Barber response:', data);
                    if (data && data.success && Array.isArray(data.barbers)) {
                        const barberSelect = $('#barber_id');
                        barberSelect.empty().append('<option value="">Choose a barber...</option>');

                        if (data.barbers.length === 0) {
                            barberSelect.html('<option value="" disabled>No barbers available for this shop</option>');
                            return;
                        }

                        data.barbers.forEach(barber => {
                            const fullName = `${barber.first_name || ''} ${barber.last_name || ''}`.trim() || 'Unnamed Barber';
                            barberSelect.append(`<option value="${barber.user_id}">${fullName}</option>`);
                        });
                    } else {
                        $('#barber_id').html('<option value="">No barbers found</option>');
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('Barber load error:', xhr.responseText);
                    $('#barber_id').html('<option value="" disabled>Error loading barbers. Retry</option>');
                });

            // Load services with robust error handling and empty-state
            console.log('Loading services for shop:', shopId);
            $.get('<?= base_url('booking/shop-services') ?>/' + shopId)
                .done(function(data) {
                    console.log('Service response:', data);
                    if (data && data.success && Array.isArray(data.services)) {
                        const serviceSelect = $('#service_id');
                        serviceSelect.empty().append('<option value="">Choose a service...</option>');

                        if (data.services.length === 0) {
                            serviceSelect.html('<option value="" disabled>No services available for this shop</option>');
                            return;
                        }

                        data.services.forEach(service => {
                            const price = Number(service.price || 0);
                            const displayPrice = new Intl.NumberFormat('en-PH', {
                                style: 'currency',
                                currency: 'PHP'
                            }).format(price);
                            serviceSelect.append(`<option value="${service.service_id}" data-price="${price}" data-duration="${service.duration}">${service.service_name} - ${displayPrice}</option>`);
                        });
                    } else {
                        $('#service_id').html('<option value="">No services found</option>');
                    }
                })
                .fail(function(xhr, status, error) {
                    console.error('Service load error:', xhr.responseText);
                    $('#service_id').html('<option value="" disabled>Error loading services. Retry</option>');
                });
        }

        // Change shop function with improved UX
        window.changeShop = function() {
            $('#appointment-details').fadeOut(300, function() {
                $('#shop-selection').fadeIn(300);
            });

            selectedShopId = null;
            selectedShopName = null;

            // Reset form and UI
            $('#bookingForm')[0].reset();
            $('#service-price').text('-');
            $('#service-duration').text('-');
            $('#appointment_time').empty().append('<option value="">Choose a time...</option>');
            $('.shop-card').removeClass('selected');
        };

        // Update service details when service is selected
        $('#service_id').change(function() {
            const option = $(this).find('option:selected');
            const price = option.data('price');
            const duration = option.data('duration');

            if (price && duration) {
                selectedService = {
                    price: price,
                    duration: duration
                };
                const displayPrice = new Intl.NumberFormat('en-PH', {
                    style: 'currency',
                    currency: 'PHP'
                }).format(Number(price || 0));
                $('#service-price').text(displayPrice);
                $('#service-duration').text(duration + ' minutes');
            } else {
                selectedService = null;
                $('#service-price').text('-');
                $('#service-duration').text('-');
            }

            // Clear time slots when service changes
            $('#appointment_time').html('<option value="">Choose a time...</option>');
        });

        // Get available time slots when date and barber are selected
        function getAvailableSlots() {
            const barberId = $('#barber_id').val();
            const date = $('#appointment_date').val();
            const serviceId = $('#service_id').val();

            console.log('getAvailableSlots called with:', {
                barberId,
                date,
                serviceId
            });

            if (!barberId || !date || !serviceId) {
                console.log('Missing required fields');
                return;
            }

            // Show loading state
            $('#appointment_time').html('<option value="">Loading available times...</option>');

            $.post('<?= base_url('booking/available-slots') ?>', {
                barber_id: barberId,
                date: date,
                service_id: serviceId
            }, function(data) {
                console.log('Response received:', data);
                if (data.success) {
                    availableSlots = data.slots;
                    updateTimeSlots();
                } else {
                    $('#appointment_time').html('<option value="">Error loading times</option>');
                    console.error('Error loading available slots:', data.error);
                }
            }).fail(function(xhr, status, error) {
                console.error('AJAX failed:', status, error);
                console.error('Response text:', xhr.responseText);
                console.error('Status code:', xhr.status);
                $('#appointment_time').html('<option value="">Error loading times</option>');
            });
        }

        // Update time slots dropdown
        function updateTimeSlots() {
            const timeSelect = $('#appointment_time');
            timeSelect.html('<option value="">Choose a time...</option>');

            if (availableSlots.length === 0) {
                timeSelect.append('<option value="" disabled>No available times for this date</option>');
                return;
            }

            // Get current date and time for client-side filtering
            const selectedDate = $('#appointment_date').val();
            const today = new Date();
            const isToday = selectedDate === today.toISOString().split('T')[0];
            const currentMinutes = today.getHours() * 60 + today.getMinutes(); // Current time in minutes

            availableSlots.forEach(slot => {
                let displayTime;
                let slotTime;

                // Handle different slot formats (object with properties or just a string)
                if (typeof slot === 'string') {
                    // Slot is just a time string like "08:00:00"
                    slotTime = slot;
                } else if (slot.time) {
                    // Slot is an object with time property
                    slotTime = slot.time;
                } else if (slot.display) {
                    slotTime = slot.display;
                } else {
                    slotTime = slot;
                }

                // Parse the time string (HH:mm:ss or HH:mm)
                const timeParts = slotTime.split(':');
                const hours = parseInt(timeParts[0]);
                const minutes = parseInt(timeParts[1] || 0);

                // REAL-TIME FILTERING: If today, check if this time is in the past
                if (isToday) {
                    const slotMinutes = hours * 60 + minutes;
                    // Skip past time slots - they should not even appear in the dropdown
                    if (slotMinutes < currentMinutes) {
                        console.log(`Blocked past time slot: ${slotTime} (Current time: ${currentMinutes} minutes from midnight)`);
                        return; // Skip this slot - don't show it
                    }
                }

                // Format as 12-hour time for display
                const period = hours >= 12 ? 'PM' : 'AM';
                const displayHours = hours === 0 ? 12 : (hours > 12 ? hours - 12 : hours);
                displayTime = `${displayHours}:${minutes.toString().padStart(2, '0')} ${period}`;

                // Add to dropdown
                const valueToUse = slotTime;
                timeSelect.append(`<option value="${valueToUse}">${displayTime}</option>`);
            });
        }

        // Event listeners for getting available slots
        $('#barber_id, #appointment_date, #service_id').change(getAvailableSlots);

        // Handle form submission with improved feedback
        $('#bookingForm').submit(function(e) {
            e.preventDefault();

            // Get selected date and time
            const selectedDate = $('#appointment_date').val();
            const selectedTime = $('#appointment_time').val();

            // REAL-TIME VALIDATION: Check if trying to book in the past
            const today = new Date();
            const todayStr = today.toISOString().split('T')[0];
            const currentMinutes = today.getHours() * 60 + today.getMinutes();

            if (selectedDate === todayStr) {
                // Parse selected time
                const timeParts = selectedTime.split(':');
                const hours = parseInt(timeParts[0]);
                const minutes = parseInt(timeParts[1]);
                const slotMinutes = hours * 60 + minutes;

                // Check if the selected time is in the past
                if (slotMinutes < currentMinutes) {
                    alert('Cannot book appointments in the past! Current time is ' + today.toLocaleTimeString() + '. Please select a future time slot.');
                    return false;
                }
            }

            // Show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Booking...').prop('disabled', true);

            const formData = new FormData(this);

            $.ajax({
                url: '<?= base_url('booking/book') ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showBookingConfirmation(response);
                    } else {
                        alert('Error: ' + response.error);
                    }
                },
                error: function() {
                    alert('An error occurred while booking the appointment.');
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Show booking confirmation modal with improved design
        function showBookingConfirmation(response) {
            const needsPay = !!response.requires_payment;
            const feeAmt = Number(response.booking_fee_amount || 0);
            const pct = Number(response.booking_fee_percentage || 0);
            const details = `
            <div class="text-center mb-4">
                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                <h4 class="text-success">Booking Created</h4>
                ${needsPay ? '<p class="mb-0">A booking fee is required to confirm.</p>' : '<p class="mb-0">No booking fee required.</p>'}
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card bg-light"><div class="card-body">
                        <h6 class="card-title"><i class="fas fa-hashtag text-primary"></i> Appointment ID</h6>
                        <p class="card-text fw-bold">#${response.appointment_id}</p>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light"><div class="card-body">
                        <h6 class="card-title"><i class="fas fa-calendar text-success"></i> Date</h6>
                        <p class="card-text fw-bold">${$('#appointment_date').val()}</p>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light"><div class="card-body">
                        <h6 class="card-title"><i class="fas fa-clock text-warning"></i> Time</h6>
                        <p class="card-text fw-bold">${$('#appointment_time option:selected').text()}</p>
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light"><div class="card-body">
                        <h6 class="card-title"><i class="fas fa-scissors text-info"></i> Service</h6>
                        <p class="card-text fw-bold">${$('#service_id option:selected').text()}</p>
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
            ${needsPay ? `<div class=\"text-end mt-3 text-muted\">Proceed with payment to confirm.</div>` : ''}
        `;

            $('#booking-details').html(details);
            $('#bookingModal').modal('show');

            // Change confirm button to proceed to payment if needed
            $('#confirm-booking').text(needsPay ? 'Proceed to Payment' : 'Confirm Booking');
            $('#confirm-booking').off('click').on('click', function() {
                if (needsPay) {
                    $.ajax({
                        url: '<?= base_url('payments/checkout') ?>' + '/' + response.appointment_id,
                        method: 'POST',
                        data: {
                            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                        },
                        success: function(res) {
                            if (res && res.success && res.checkout_url) {
                                window.location.href = res.checkout_url;
                            } else {
                                alert('Payment error: ' + (res.error || 'Unable to start checkout'));
                            }
                        },
                        error: function(xhr) {
                            const msg = (xhr && xhr.responseText) ? xhr.responseText : 'Payment error';
                            alert(msg);
                        }
                    });
                } else {
                    $('#bookingModal').modal('hide');
                    $('body').append('<div class="alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999;">Booking confirmed successfully!</div>');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            });
        }

        // Confirm booking
        $('#confirm-booking').click(function() {
            $('#bookingModal').modal('hide');
            // Show success message before reload
            $('body').append('<div class="alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999;">Booking confirmed successfully!</div>');
            setTimeout(() => {
                location.reload();
            }, 1500);
        });

        // Load user's appointments with improved display
        function loadAppointments() {
            $.get('<?= base_url('booking/my-appointments') ?>', function(data) {
                if (data.success && data.appointments.length > 0) {
                    let html = '';
                    data.appointments.slice(0, 5).forEach(appointment => {
                        const date = new Date(appointment.appointment_date);
                        const formattedDate = date.toLocaleDateString();
                        const statusClass = 'status-' + appointment.status;

                        html += `
                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded bg-light">
                            <div>
                                <strong class="text-dark">${formattedDate}</strong><br>
                                <small class="text-muted">${appointment.appointment_time}</small>
                            </div>
                            <span class="status-badge ${statusClass}">${appointment.status}</span>
                        </div>
                    `;
                    });
                    $('#appointments-list').html(html);
                }
            });
        }

        // Load appointments on page load
        loadAppointments();
    });
</script>
<?= $this->endSection() ?>