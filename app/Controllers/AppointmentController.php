<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\UserModel;
use App\Models\ServiceModel;
use App\Models\ShopModel;
use App\Models\EmployeeModel;
use App\Models\NotificationModel;
use App\Models\EarningsModel;
use App\Models\CommissionSettingsModel;

class AppointmentController extends BaseController
{
    protected $appointmentModel;
    protected $userModel;
    protected $serviceModel;
    protected $shopModel;
    protected $employeeModel;
    protected $notificationModel;
    protected $earningsModel;
    protected $commissionSettingsModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->userModel = new UserModel();
        $this->serviceModel = new ServiceModel();
        $this->shopModel = new ShopModel();
        $this->employeeModel = new EmployeeModel();
        $this->notificationModel = new NotificationModel();
        $this->earningsModel = new EarningsModel();
        $this->commissionSettingsModel = new CommissionSettingsModel();
    }

    // Show appointment management page
    public function index()
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('role') ?? session()->get('user_role');

        switch ($userRole) {
            case 'customer':
                return $this->customerAppointments($userId);
            case 'barber':
                return $this->barberAppointments($userId);
            case 'owner':
                return $this->ownerAppointments($userId);
            case 'admin':
                return $this->adminAppointments();
            default:
                return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }
    }

    // Customer view of their appointments
    private function customerAppointments($userId)
    {
        $appointments = $this->getAppointmentsWithDetails('customer', $userId);
        
        $data = [
            'title' => 'My Appointments',
            'user_role' => 'customer',
            'appointments' => $appointments,
            'status_counts' => $this->getStatusCounts('customer', $userId)
        ];

        return view('appointments/index', $data);
    }

    // Barber view of their assigned appointments
    private function barberAppointments($userId)
    {
        $appointments = $this->getAppointmentsWithDetails('barber', $userId);
        
        $data = [
            'title' => 'My Appointments',
            'user_role' => 'barber',
            'appointments' => $appointments,
            'status_counts' => $this->getStatusCounts('barber', $userId)
        ];

        return view('appointments/index', $data);
    }

    // Shop owner view of all shop appointments
    private function ownerAppointments($userId)
    {
        // Get owner's shop
        $shop = $this->shopModel->where('owner_id', $userId)->first();
        if (!$shop) {
            return redirect()->to('/dashboard')->with('error', 'Shop not found.');
        }

        $appointments = $this->getShopAppointments($shop['shop_id']);
        
        $data = [
            'title' => 'Shop Appointments',
            'user_role' => 'owner',
            'shop' => $shop,
            'appointments' => $appointments,
            'status_counts' => $this->getShopStatusCounts($shop['shop_id'])
        ];

        return view('appointments/index', $data);
    }

    // Admin view of all appointments
    private function adminAppointments()
    {
        $appointments = $this->getAppointmentsWithDetails('admin');
        
        $data = [
            'title' => 'All Appointments',
            'user_role' => 'admin',
            'appointments' => $appointments,
            'status_counts' => $this->getStatusCounts('admin')
        ];

        return view('appointments/index', $data);
    }

    // Update appointment status with proper workflow
    public function updateStatus($appointmentId)
    {
        $newStatus = $this->request->getPost('status');
        $notes = $this->request->getPost('notes') ?? '';
        
        if (!in_array($newStatus, ['pending', 'confirmed', 'completed', 'cancelled'])) {
            return $this->response->setJSON(['error' => 'Invalid status']);
        }

        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            return $this->response->setJSON(['error' => 'Appointment not found']);
        }

        // Check permissions
        if (!$this->canUpdateStatus($appointment, $newStatus)) {
            return $this->response->setJSON(['error' => 'Unauthorized to change status']);
        }

        // Validate status workflow
        if (!$this->isValidStatusTransition($appointment['status'], $newStatus)) {
            return $this->response->setJSON(['error' => 'Invalid status transition']);
        }

        // Update appointment
        $updateData = ['status' => $newStatus];
        if ($notes) {
            $updateData['notes'] = $notes;
        }

        $result = $this->appointmentModel->update($appointmentId, $updateData);

        if ($result) {
            // Log status change
            $this->logStatusChange($appointmentId, $appointment['status'], $newStatus);
            
            // Create notifications for status change
            $this->createStatusChangeNotifications($appointmentId, $newStatus);
            
            // Create earnings record when appointment is completed
            if ($newStatus === 'completed') {
                $this->createEarningsRecord($appointmentId);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => "Appointment {$newStatus} successfully"
            ]);
        } else {
            return $this->response->setJSON(['error' => 'Failed to update appointment']);
        }
    }

    // Check if user can update appointment status
    private function canUpdateStatus($appointment, $newStatus)
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('role') ?? session()->get('user_role');

        switch ($userRole) {
            case 'admin':
                return true; // Admin can change any status
            
            case 'owner':
                // Owner can change status for appointments in their shop
                $employee = $this->employeeModel->where('user_id', $appointment['barber_id'])->first();
                if ($employee) {
                    $shop = $this->shopModel->find($employee['shop_id']);
                    return $shop && $shop['owner_id'] == $userId;
                }
                return false;
            
            case 'barber':
                // Barber can only change status for their own appointments
                return $appointment['barber_id'] == $userId;
            
            case 'customer':
                // Customer can only cancel their own pending/confirmed appointments
                return $appointment['customer_id'] == $userId && 
                       $newStatus === 'cancelled' && 
                       in_array($appointment['status'], ['pending', 'confirmed']);
            
            default:
                return false;
        }
    }

    // Validate status transition workflow
    private function isValidStatusTransition($currentStatus, $newStatus)
    {
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
            'completed' => [], // Cannot change from completed
            'cancelled' => ['pending'] // Can reschedule cancelled appointments
        ];

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }

    // Get appointments with full details
    private function getAppointmentsWithDetails($role, $userId = null)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('appointments a')
            ->select('a.*, 
                     c.first_name as customer_first_name, c.last_name as customer_last_name, c.email as customer_email,
                     b.first_name as barber_first_name, b.last_name as barber_last_name, b.email as barber_email,
                     s.service_name, s.price, s.duration,
                     sh.shop_name')
            ->join('users c', 'c.user_id = a.customer_id')
            ->join('users b', 'b.user_id = a.barber_id')
            ->join('services s', 's.service_id = a.service_id')
            ->join('employees e', 'e.user_id = a.barber_id')
            ->join('shops sh', 'sh.shop_id = e.shop_id');

        switch ($role) {
            case 'customer':
                $builder->where('a.customer_id', $userId);
                break;
            case 'barber':
                $builder->where('a.barber_id', $userId);
                break;
            case 'admin':
                // No additional filter for admin
                break;
        }

        return $builder->orderBy('a.appointment_date', 'DESC')
                      ->orderBy('a.appointment_time', 'DESC')
                      ->get()
                      ->getResultArray();
    }

    // Get shop appointments
    private function getShopAppointments($shopId)
    {
        $db = \Config\Database::connect();
        
        return $db->table('appointments a')
            ->select('a.*, 
                     c.first_name as customer_first_name, c.last_name as customer_last_name, c.email as customer_email,
                     b.first_name as barber_first_name, b.last_name as barber_last_name, b.email as barber_email,
                     s.service_name, s.price, s.duration')
            ->join('users c', 'c.user_id = a.customer_id')
            ->join('users b', 'b.user_id = a.barber_id')
            ->join('services s', 's.service_id = a.service_id')
            ->join('employees e', 'e.user_id = a.barber_id')
            ->where('e.shop_id', $shopId)
            ->orderBy('a.appointment_date', 'DESC')
            ->orderBy('a.appointment_time', 'DESC')
            ->get()
            ->getResultArray();
    }

    // Get status counts for dashboard
    private function getStatusCounts($role, $userId = null)
    {
        $baseConditions = [];
        
        switch ($role) {
            case 'customer':
                $baseConditions['customer_id'] = $userId;
                break;
            case 'barber':
                $baseConditions['barber_id'] = $userId;
                break;
        }

        return [
            'pending' => $this->appointmentModel->where($baseConditions)->where('status', 'pending')->countAllResults(false),
            'confirmed' => $this->appointmentModel->where($baseConditions)->where('status', 'confirmed')->countAllResults(false),
            'completed' => $this->appointmentModel->where($baseConditions)->where('status', 'completed')->countAllResults(false),
            'cancelled' => $this->appointmentModel->where($baseConditions)->where('status', 'cancelled')->countAllResults(false),
            'total' => $this->appointmentModel->where($baseConditions)->countAllResults(false)
        ];
    }

    // Get shop status counts
    private function getShopStatusCounts($shopId)
    {
        $db = \Config\Database::connect();
        
        $baseQuery = $db->table('appointments a')
            ->join('employees e', 'e.user_id = a.barber_id')
            ->where('e.shop_id', $shopId);

        return [
            'pending' => (clone $baseQuery)->where('a.status', 'pending')->countAllResults(),
            'confirmed' => (clone $baseQuery)->where('a.status', 'confirmed')->countAllResults(),
            'completed' => (clone $baseQuery)->where('a.status', 'completed')->countAllResults(),
            'cancelled' => (clone $baseQuery)->where('a.status', 'cancelled')->countAllResults(),
            'total' => (clone $baseQuery)->countAllResults()
        ];
    }

    // Create notifications for status changes
    private function createStatusChangeNotifications($appointmentId, $newStatus)
    {
        switch ($newStatus) {
            case 'confirmed':
                $this->notificationModel->createAppointmentNotification($appointmentId, 'appointment_confirmed');
                break;
            case 'cancelled':
                $this->notificationModel->createAppointmentNotification($appointmentId, 'appointment_cancelled');
                break;
            case 'completed':
                $this->notificationModel->createAppointmentNotification($appointmentId, 'appointment_completed');
                break;
        }
    }

    // Log status changes for audit
    private function logStatusChange($appointmentId, $oldStatus, $newStatus)
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('role') ?? session()->get('user_role');
        
        log_message('info', "Appointment {$appointmentId} status changed from {$oldStatus} to {$newStatus} by user {$userId} ({$userRole})");
        
        // You can also store this in a dedicated audit table if needed
    }

    // Get appointment details for modal/popup
    public function getDetails($appointmentId)
    {
        $appointment = $this->getAppointmentsWithDetails('admin');
        $appointment = array_filter($appointment, function($a) use ($appointmentId) {
            return $a['appointment_id'] == $appointmentId;
        });
        
        $appointment = array_shift($appointment);
        
        if (!$appointment) {
            return $this->response->setJSON(['error' => 'Appointment not found']);
        }

        return $this->response->setJSON([
            'success' => true,
            'appointment' => $appointment
        ]);
    }

    // Create earnings record when appointment is completed
    private function createEarningsRecord($appointmentId)
    {
        try {
            // Get appointment details
            $appointment = $this->appointmentModel->getAppointmentWithDetails($appointmentId);
            if (!$appointment) {
                log_message('error', "Cannot create earnings: Appointment {$appointmentId} not found");
                return false;
            }

            // Get the barber's shop to determine commission settings
            $employee = $this->employeeModel->where('user_id', $appointment['barber_id'])->first();
            if (!$employee) {
                log_message('error', "Cannot create earnings: Barber {$appointment['barber_id']} not found in employees table");
                return false;
            }

            // Get commission settings for the shop
            $commissionSettings = $this->commissionSettingsModel->getShopCommissionSettings($employee['shop_id']);
            $barberCommissionRate = $commissionSettings['barber_commission_rate'];

            // Calculate commission amount
            $totalAmount = $appointment['total_amount'];
            $barberCommissionAmount = ($totalAmount * $barberCommissionRate) / 100;

            // Create earnings record for barber
            $earningsData = [
                'barber_id'        => $appointment['barber_id'],
                'appointment_id'   => $appointmentId,
                'service_id'       => $appointment['service_id'],
                'amount'           => $totalAmount,
                'commission_rate'  => $barberCommissionRate,
                'commission_amount' => $barberCommissionAmount,
                'payment_method'   => 'cash', // Default, can be updated later
                'earning_date'     => $appointment['appointment_date'],
            ];

            $result = $this->earningsModel->insert($earningsData);
            
            if ($result) {
                log_message('info', "Earnings record created for appointment {$appointmentId}, barber gets ₱{$barberCommissionAmount}");
                
                // Create haircut history record
                $this->createHaircutHistory($appointmentId);
                
                return true;
            } else {
                log_message('error', "Failed to create earnings record for appointment {$appointmentId}");
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', "Error creating earnings record: " . $e->getMessage());
            return false;
        }
    }

    // Create haircut history when appointment is completed
    private function createHaircutHistory($appointmentId)
    {
        try {
            $haircutHistoryModel = new \App\Models\HaircutHistoryModel();
            return $haircutHistoryModel->addFromAppointment($appointmentId);
        } catch (\Exception $e) {
            log_message('error', "Error creating haircut history: " . $e->getMessage());
            return false;
        }
    }
}
