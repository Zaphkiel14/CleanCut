<?php

namespace App\Models;

use CodeIgniter\Model;

class AppointmentModel extends Model
{
    protected $table = 'appointments';
    protected $primaryKey = 'appointment_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'customer_id', 'barber_id', 'service_id', 'appointment_date', 
        'appointment_time', 'status', 'notes', 'total_amount',
        // booking fee fields
        'booking_fee_percentage', 'booking_fee_amount', 'booking_fee_source',
        // payment tracking fields
        'payment_status', 'payment_provider', 'payment_reference', 'payment_receipt_url'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'customer_id' => 'required|integer',
        'barber_id' => 'required|integer',
        'service_id' => 'required|integer',
        'appointment_date' => 'required|valid_date',
        'appointment_time' => 'required',
        'status' => 'required|in_list[pending,confirmed,completed,cancelled]'
    ];

    protected $validationMessages = [
        'customer_id' => [
            'required' => 'Customer is required.'
        ],
        'barber_id' => [
            'required' => 'Barber is required.'
        ],
        'service_id' => [
            'required' => 'Service is required.'
        ],
        'appointment_date' => [
            'required' => 'Appointment date is required.',
            'valid_date' => 'Please enter a valid date.'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Helper methods
    public function getAppointmentsByUser($userId)
    {
        return $this->where('customer_id', $userId)
                    ->orderBy('appointment_date', 'ASC')
                    ->orderBy('appointment_time', 'ASC')
                    ->findAll();
    }

    public function getAppointmentsByDate($date)
    {
        return $this->where('appointment_date', $date)
                    ->orderBy('appointment_time', 'ASC')
                    ->findAll();
    }

    public function isTimeSlotAvailable($barberId, $date, $time, $excludeAppointmentId = null)
    {
        $builder = $this->where('barber_id', $barberId)
                        ->where('appointment_date', $date)
                        ->where('appointment_time', $time)
                        ->where('status !=', 'cancelled');

        if ($excludeAppointmentId) {
            $builder->where('appointment_id !=', $excludeAppointmentId);
        }

        return $builder->countAllResults() === 0;
    }

    public function getUpcomingAppointments($userId, $limit = 5)
    {
        $today = date('Y-m-d');
        return $this->where('customer_id', $userId)
                    ->where('appointment_date >=', $today)
                    ->where('status !=', 'cancelled')
                    ->orderBy('appointment_date', 'ASC')
                    ->orderBy('appointment_time', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getAppointmentWithDetails($appointmentId)
    {
        $db = \Config\Database::connect();
        
        return $db->table('appointments a')
                  ->select('a.*, u.first_name as customer_name, b.first_name as barber_name, s.service_name, s.price')
                  ->join('users u', 'u.user_id = a.customer_id')
                  ->join('users b', 'b.user_id = a.barber_id')
                  ->join('services s', 's.service_id = a.service_id')
                  ->where('a.appointment_id', $appointmentId)
                  ->get()
                  ->getRowArray();
    }

    public function updateStatus($appointmentId, $status)
    {
        return $this->update($appointmentId, ['status' => $status]);
    }

    public function getAppointmentStats($userId = null, $dateRange = null)
    {
        $builder = $this;
        
        if ($userId) {
            $builder->where('customer_id', $userId);
        }
        
        if ($dateRange) {
            $builder->where('appointment_date >=', $dateRange['start'])
                    ->where('appointment_date <=', $dateRange['end']);
        }
        
        $stats = [
            'total' => $builder->countAllResults(),
            'pending' => $builder->where('status', 'pending')->countAllResults(),
            'confirmed' => $builder->where('status', 'confirmed')->countAllResults(),
            'completed' => $builder->where('status', 'completed')->countAllResults(),
            'cancelled' => $builder->where('status', 'cancelled')->countAllResults()
        ];
        
        return $stats;
    }

    public function getBarberAppointments($barberId, $date = null)
    {
        $builder = $this->where('barber_id', $barberId);
        
        if ($date) {
            $builder->where('appointment_date', $date);
        }
        
        return $builder->orderBy('appointment_date', 'ASC')
                       ->orderBy('appointment_time', 'ASC')
                       ->findAll();
    }

        public function getTodayAppointments($barberId = null)
    {
        $today = date('Y-m-d');
        $builder = $this->where('appointment_date', $today)
                        ->where('status !=', 'cancelled');

        if ($barberId) {
            $builder->where('barber_id', $barberId);
        }

        return $builder->orderBy('appointment_time', 'ASC')->findAll();
    }

    // Get barber appointment count
    public function getBarberAppointmentCount($barberId)
    {
        return $this->where('barber_id', $barberId)
                    ->where('status', 'completed')
                    ->countAllResults();
    }

    // Get barber total earnings
    public function getBarberTotalEarnings($barberId)
    {
        $result = $this->select('SUM(total_amount) as total_earnings')
                       ->where('barber_id', $barberId)
                       ->where('status', 'completed')
                       ->first();
        
        return $result ? $result['total_earnings'] : 0;
    }

    // Get customer appointment count
    public function getCustomerAppointmentCount($customerId)
    {
        return $this->where('customer_id', $customerId)
                    ->where('status', 'completed')
                    ->countAllResults();
    }

    // Get customer total spent
    public function getCustomerTotalSpent($customerId)
    {
        $result = $this->select('SUM(total_amount) as total_spent')
                       ->where('customer_id', $customerId)
                       ->where('status', 'completed')
                       ->first();
        
        return $result ? $result['total_spent'] : 0;
    }

    // Get completed appointments by barber
    public function getCompletedAppointmentsByBarber($barberId)
    {
        return $this->select('appointments.*, 
                             u1.first_name as customer_first_name, u1.last_name as customer_last_name,
                             s.service_name')
                    ->join('users u1', 'u1.user_id = appointments.customer_id')
                    ->join('services s', 's.service_id = appointments.service_id')
                    ->where('appointments.barber_id', $barberId)
                    ->where('appointments.status', 'completed')
                    ->orderBy('appointments.appointment_date DESC')
                    ->findAll();
    }
} 