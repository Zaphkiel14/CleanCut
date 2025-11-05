<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\ServiceModel;
use App\Models\UserModel;
use App\Models\ShopModel;
use App\Models\EmployeeModel;
use App\Models\ScheduleModel;
use App\Models\NotificationModel;
use App\Models\EarningsModel;
use App\Models\HaircutHistoryModel;

class BookingController extends BaseController
{
    protected $appointmentModel;
    protected $serviceModel;
    protected $userModel;
    protected $shopModel;
    protected $employeeModel;
    protected $scheduleModel;
    protected $notificationModel;
    protected $earningsModel;
    protected $haircutHistoryModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->serviceModel = new ServiceModel();
        $this->userModel = new UserModel();
        $this->shopModel = new ShopModel();
        $this->employeeModel = new EmployeeModel();
        $this->scheduleModel = new ScheduleModel();
        $this->notificationModel = new NotificationModel();
        $this->earningsModel = new EarningsModel();
        $this->haircutHistoryModel = new HaircutHistoryModel();
    }

    // Show booking page
    public function index()
    {
        $shopId = $this->request->getGet('shop_id') ?? null;
        
        $data = [
            'title' => 'Book Appointment',
            'shops' => $this->shopModel->getActiveShops(),
            'selectedShop' => $shopId ? $this->shopModel->find($shopId) : null,
            'barbers' => $shopId ? $this->getBarbersForShop($shopId) : [],
            'services' => $shopId ? $this->serviceModel->getActiveServices($shopId) : [],
        ];

        return view('booking/index', $data);
    }

    // Show available time slots (REAL-TIME)
    public function getAvailableSlots()
    {
        $barberId = $this->request->getPost('barber_id');
        $date = $this->request->getPost('date');
        $serviceId = $this->request->getPost('service_id');

        if (!$barberId || !$date || !$serviceId) {
            return $this->response->setJSON(['error' => 'Missing required parameters']);
        }

        $service = $this->serviceModel->getServiceById($serviceId);
        if (!is_array($service) || empty($service)) {
            return $this->response->setJSON(['error' => 'Service not found']);
        }

        // Get available slots from availability table
        $db = \Config\Database::connect();
        
        // Check if table exists
        if (!$db->tableExists('availability')) {
            return $this->response->setJSON([
                'success' => true,
                'slots' => [],
                'message' => 'No availability table found'
            ]);
        }
        
        $availabilityRecords = $db->table('availability')
            ->where('user_id', (int) $barberId)
            ->where('available_date', $date)
            ->orderBy('available_time', 'ASC')
            ->get()
            ->getResultArray();

        $availableSlots = [];
        
        // Additional real-time filtering for extra security
        $today = date('Y-m-d');
        $currentTime = time();
        
        foreach ($availabilityRecords as $record) {
            $slot = $record['available_time'];
            if ($date === $today) {
                $slotDateTime = strtotime($date . ' ' . $slot);
                if ($slotDateTime > $currentTime) {
                    $availableSlots[] = $slot;
                }
            } else {
                $availableSlots[] = $slot;
            }
        }

        // Fallback: if no explicit availability saved for the date, generate from weekly schedule
        if (empty($availableSlots)) {
            $dayOfWeek = strtolower(date('l', strtotime($date))); // monday, tuesday, ...
            $weekly = $this->scheduleModel
                ->where('user_id', (int) $barberId)
                ->where('day_of_week', $dayOfWeek)
                ->first();
            if ($weekly && (int) ($weekly['is_available'] ?? 0) === 1) {
                $interval = 30; // default to 30 minutes; could use (int)($service['duration'] ?? 30)
                $startTime = new \DateTime($weekly['start_time']);
                $endTime = new \DateTime($weekly['end_time']);
                $cursor = clone $startTime;
                while ($cursor < $endTime) {
                    $slot = $cursor->format('H:i');
                    $slotDateTime = strtotime($date . ' ' . $slot);
                    if ($date !== $today || $slotDateTime > $currentTime) {
                        $availableSlots[] = $slot;
                    }
                    $cursor->modify("+{$interval} minutes");
                }
            }
        }

        // Remove slots that are already booked for this barber and date
        try {
            $existingAppointments = $db->table('appointments')
                ->select('appointment_time')
                ->where('barber_id', (int) $barberId)
                ->where('appointment_date', $date)
                ->where('status !=', 'cancelled')
                ->get()
                ->getResultArray();

            if (!empty($existingAppointments) && !empty($availableSlots)) {
                $bookedTimes = array_map(function ($row) {
                    $time = $row['appointment_time'] ?? '';
                    // Normalize to HH:MM for comparison
                    return substr($time, 0, 5);
                }, $existingAppointments);

                $availableSlots = array_values(array_filter($availableSlots, function ($slot) use ($bookedTimes) {
                    $normalized = substr((string) $slot, 0, 5);
                    return !in_array($normalized, $bookedTimes, true);
                }));
            }
        } catch (\Throwable $e) {
            // If filtering fails for any reason, keep original list but log for diagnostics
            log_message('error', 'Failed to filter booked time slots: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success' => true,
            'slots' => $availableSlots,
            'current_time' => date('Y-m-d H:i:s'), // For debugging
            'selected_date' => $date
        ]);
    }

    // Book appointment
    public function book()
    {
        // Check if user is logged in
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON([
                'error' => 'You must be logged in to book an appointment'
            ]);
        }

        $rules = [
            'barber_id' => 'required|integer',
            'service_id' => 'required|integer',
            'appointment_date' => 'required|valid_date',
            'appointment_time' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'error' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $data = $this->request->getPost();
        
        // Validate required fields
        if (empty($data['barber_id']) || empty($data['service_id']) || empty($data['appointment_date']) || empty($data['appointment_time'])) {
            return $this->response->setJSON([
                'error' => 'All required fields must be filled'
            ]);
        }

        // Persist correct fields expected by the appointments table/model
        $data['customer_id'] = (int) $userId;
        
        // Lookup service to get pricing
        $service = $this->serviceModel->getServiceById((int) $data['service_id']);
        if (!is_array($service) || empty($service)) {
            return $this->response->setJSON(['error' => 'Service not found']);
        }
        
        $data['total_amount'] = (float) $service['price'];
        $data['status'] = $data['status'] ?? 'pending';
        
        // Handle appointment notes
        $data['haircut_type'] = $this->request->getPost('haircut_type') ?? null;
        $data['urgency'] = $this->request->getPost('urgency') ?? 'normal';
        $data['appointment_notes'] = $this->request->getPost('appointment_notes') ?? null;

        // Compute booking fee based on shop vs freelance barber
        $employee = $this->employeeModel->where('user_id', (int)$data['barber_id'])->first();
        $bookingFeePct = 0.00;
        $bookingFeeSource = null;
        if ($employee && isset($employee['shop_id'])) {
            $shop = $this->shopModel->find($employee['shop_id']);
            if ($shop && isset($shop['booking_fee_percentage'])) {
                $bookingFeePct = (float)$shop['booking_fee_percentage'];
                $bookingFeeSource = 'owner';
            }
        } else {
            $barber = $this->userModel->find((int)$data['barber_id']);
            if ($barber && isset($barber['freelance_booking_fee_percentage'])) {
                $bookingFeePct = (float)$barber['freelance_booking_fee_percentage'];
                $bookingFeeSource = 'barber';
            }
        }
        $bookingFeeAmt = round(((float)$data['total_amount']) * ($bookingFeePct / 100.0), 2);
        $data['booking_fee_percentage'] = number_format($bookingFeePct, 2, '.', '');
        $data['booking_fee_amount'] = number_format($bookingFeeAmt, 2, '.', '');
        $data['booking_fee_source'] = $bookingFeeSource;
        $data['payment_status'] = $bookingFeeAmt > 0 ? 'pending' : 'none';

        // REAL-TIME VALIDATION: Check if appointment is in the past
        $appointmentDateTime = strtotime($data['appointment_date'] . ' ' . $data['appointment_time']);
        $currentDateTime = time();
        $timeDifference = $appointmentDateTime - $currentDateTime;
        
        log_message('info', 'Booking validation: Appointment time = ' . date('Y-m-d H:i:s', $appointmentDateTime) . ', Current time = ' . date('Y-m-d H:i:s', $currentDateTime) . ', Difference = ' . $timeDifference . ' seconds');
        
        // If the appointment date/time is in the past, reject it
        if ($timeDifference <= 0) {
            return $this->response->setJSON([
                'error' => 'Cannot book appointments in the past. Please select a future date and time.',
                'appointment_time' => date('Y-m-d H:i:s', $appointmentDateTime),
                'current_time' => date('Y-m-d H:i:s', $currentDateTime),
                'difference_seconds' => $timeDifference
            ]);
        }

        // Check if time slot is available
        if (!$this->appointmentModel->isTimeSlotAvailable(
            $data['barber_id'], 
            $data['appointment_date'], 
            $data['appointment_time']
        )) {
            return $this->response->setJSON([
                'error' => 'Selected time slot is not available'
            ]);
        }

        // Create appointment (handle race condition if the slot was just taken)
        try {
            $appointmentId = $this->appointmentModel->insert($data);
        } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
            if (stripos($e->getMessage(), 'Duplicate') !== false) {
                return $this->response->setJSON([
                    'error' => 'That time slot was just booked by someone else. Please pick another time.'
                ]);
            }
            // Log the actual error for debugging
            log_message('error', 'Booking error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Database error occurred while booking appointment'
            ]);
        } catch (\Exception $e) {
            // Log any other errors
            log_message('error', 'Unexpected booking error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'An unexpected error occurred while booking appointment'
            ]);
        }

        if ($appointmentId) {
            // Create notification for new appointment
            try {
                $this->notificationModel->createAppointmentNotification($appointmentId, 'new_appointment');
                
                // Send email notification
                $notificationController = new \App\Controllers\NotificationController();
                $notificationController->sendEmailNotification($appointmentId, 'booking');
            } catch (\Exception $e) {
                // Don't fail the booking if notification fails
                log_message('warning', 'Failed to create notification: ' . $e->getMessage());
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Appointment booked successfully',
                'appointment_id' => $appointmentId,
                'booking_fee_percentage' => (float)$data['booking_fee_percentage'],
                'booking_fee_amount' => (float)$data['booking_fee_amount'],
                'total_amount' => (float)$data['total_amount'],
                'requires_payment' => $bookingFeeAmt > 0
            ]);
        } else {
            return $this->response->setJSON([
                'error' => 'Failed to book appointment'
            ]);
        }
    }

    // Cancel appointment
    public function cancel($appointmentId)
    {
        $appointment = $this->appointmentModel->find($appointmentId);
        
        if (!$appointment) {
            return $this->response->setJSON(['error' => 'Appointment not found']);
        }

        // Check if user owns this appointment or is admin
        $userId = session()->get('user_id');
        $userRole = session()->get('role');
        
        if ($appointment['user_id'] != $userId && $userRole !== 'admin' && $userRole !== 'owner') {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        // Free the time slot by removing the row (kept simple; audit can log if needed)
        $result = $this->appointmentModel->delete($appointmentId);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Appointment cancelled successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'error' => 'Failed to cancel appointment'
            ]);
        }
    }

    // Confirm appointment (for barbers/admins)
    public function confirm($appointmentId)
    {
        $appointment = $this->appointmentModel->find($appointmentId);
        
        if (!$appointment) {
            return $this->response->setJSON(['error' => 'Appointment not found']);
        }

        // Check if user is the barber or admin/owner
        $userId = session()->get('user_id');
        $userRole = session()->get('role');
        
        if ($appointment['barber_id'] != $userId && $userRole !== 'admin' && $userRole !== 'owner') {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $result = $this->appointmentModel->updateStatus($appointmentId, 'confirmed');

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Appointment confirmed successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'error' => 'Failed to confirm appointment'
            ]);
        }
    }

    // Complete appointment
    public function complete($appointmentId)
    {
        $appointment = $this->appointmentModel->find($appointmentId);
        
        if (!$appointment) {
            return $this->response->setJSON(['error' => 'Appointment not found']);
        }

        // Check if user is the barber or admin/owner
        $userId = session()->get('user_id');
        $userRole = session()->get('role');
        
        if ($appointment['barber_id'] != $userId && $userRole !== 'admin' && $userRole !== 'owner') {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $result = $this->appointmentModel->updateStatus($appointmentId, 'completed');

        if (!$result) {
            return $this->response->setJSON([
                'error' => 'Failed to complete appointment'
            ]);
        }

        // Create earnings and history records to reflect in analytics
        try {
            $appointmentDetails = $this->appointmentModel->getAppointmentWithDetails($appointmentId);
            if ($appointmentDetails) {
                $commissionRate = 70; // default barber commission percentage
                $barberCommissionAmount = ($appointmentDetails['total_amount'] * $commissionRate) / 100;

                $earningsData = [
                    'barber_id' => $appointmentDetails['barber_id'],
                    'appointment_id' => $appointmentId,
                    'service_id' => $appointmentDetails['service_id'],
                    'amount' => $appointmentDetails['total_amount'],
                    'commission_rate' => $commissionRate,
                    'commission_amount' => $barberCommissionAmount,
                    'payment_method' => 'cash',
                    'payment_status' => 'completed',
                    'earning_date' => $appointmentDetails['appointment_date'],
                ];

                $this->earningsModel->insert($earningsData);

                // Also record haircut history
                $this->haircutHistoryModel->addFromAppointment($appointmentId);
            }
        } catch (\Exception $e) {
            log_message('error', 'Booking complete: failed to create earnings/history for appointment ' . $appointmentId . ' - ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Appointment completed successfully'
        ]);
    }

    // Get appointment details
    public function getAppointment($appointmentId)
    {
        $appointment = $this->appointmentModel->getAppointmentWithDetails($appointmentId);
        
        if (!$appointment) {
            return $this->response->setJSON(['error' => 'Appointment not found']);
        }

        return $this->response->setJSON([
            'success' => true,
            'appointment' => $appointment
        ]);
    }

    // Get user appointments
    public function getUserAppointments()
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('role');
        
        if ($userRole === 'barber') {
            $appointments = $this->appointmentModel->getBarberAppointments($userId);
        } else {
            $appointments = $this->appointmentModel->getAppointmentsByUser($userId);
        }

        return $this->response->setJSON([
            'success' => true,
            'appointments' => $appointments
        ]);
    }

    // Get barbers for a specific shop (AJAX)
    public function getShopBarbers($shopId)
    {
        $barbers = $this->getBarbersForShop($shopId);
        
        return $this->response->setJSON([
            'success' => true,
            'barbers' => $barbers
        ]);
    }

    // Get services for a specific shop (AJAX)
    public function getServicesForShop($shopId)
    {
        $services = $this->serviceModel->getActiveServices($shopId);
        
        return $this->response->setJSON([
            'success' => true,
            'services' => $services
        ]);
    }

    // Validate if a time slot is available in real-time (API endpoint)
    public function validateTimeSlot()
    {
        $barberId = $this->request->getPost('barber_id');
        $date = $this->request->getPost('date');
        $time = $this->request->getPost('time');
        
        if (!$barberId || !$date || !$time) {
            return $this->response->setJSON([
                'valid' => false,
                'error' => 'Missing required parameters'
            ]);
        }
        
        // Check if the time slot is in the past (REAL-TIME CHECK)
        $appointmentDateTime = strtotime($date . ' ' . $time);
        $currentDateTime = time();
        
        if ($appointmentDateTime <= $currentDateTime) {
            return $this->response->setJSON([
                'valid' => false,
                'error' => 'Cannot book appointments in the past',
                'appointment_time' => date('Y-m-d H:i:s', $appointmentDateTime),
                'current_time' => date('Y-m-d H:i:s', $currentDateTime)
            ]);
        }
        
        // Check if time slot is already booked
        if (!$this->appointmentModel->isTimeSlotAvailable($barberId, $date, $time)) {
            return $this->response->setJSON([
                'valid' => false,
                'error' => 'Time slot is already booked'
            ]);
        }
        
        return $this->response->setJSON([
            'valid' => true,
            'message' => 'Time slot is available',
            'current_time' => date('Y-m-d H:i:s')
        ]);
    }

    // Helper method to get barbers for a specific shop
    private function getBarbersForShop($shopId)
    {
        $employees = $this->employeeModel->getEmployeesByShop($shopId);
        $barberIds = array_column($employees, 'user_id');
        
        if (empty($barberIds)) {
            return [];
        }
        
        return $this->userModel->whereIn('user_id', $barberIds)
                               ->where('role', 'barber')
                               ->where('is_active', 1)
                               ->findAll();
    }

    // Client view for booking
    public function clientView()
    {
        $data = [
            'title' => 'Book Your Appointment'
        ];

        return view('booking/client-view', $data);
    }

    // Get shops for client booking
    public function getShops()
    {
        try {
            $db = \Config\Database::connect();
            $shops = $db->table('shops')
                       ->where('is_active', 1)
                       ->get()
                       ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'shops' => $shops
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to load shops: ' . $e->getMessage()
            ]);
        }
    }

    // Get client's appointments
    public function getMyAppointments()
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $userId = session()->get('user_id');

        try {
            $db = \Config\Database::connect();
            $appointments = $db->table('appointments a')
                             ->select('
                                 a.*, 
                                 u.first_name, u.last_name,
                                 s.service_name, s.price, s.duration,
                                 sh.shop_name
                             ')
                             ->join('users u', 'u.user_id = a.barber_id')
                             ->join('services s', 's.service_id = a.service_id')
                             ->join('shops sh', 'sh.shop_id = a.shop_id')
                             ->where('a.customer_id', $userId)
                             ->orderBy('a.appointment_date', 'DESC')
                             ->orderBy('a.appointment_time', 'DESC')
                             ->get()
                             ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'appointments' => $appointments
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to load appointments: ' . $e->getMessage()
            ]);
        }
    }
} 