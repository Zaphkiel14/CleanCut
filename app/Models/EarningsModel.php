<?php

namespace App\Models;

use CodeIgniter\Model;

class EarningsModel extends Model
{
    protected $table      = 'earnings';
    protected $primaryKey = 'earning_id';

    protected $allowedFields = [
        'barber_id',
        'appointment_id',
        'service_id',
        'amount',
        'commission_rate',
        'commission_amount',
        'payment_method',
        'earning_date',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';

    // Validation rules
    protected $validationRules = [
        'barber_id'        => 'required|integer',
        'appointment_id'   => 'required|integer',
        'service_id'       => 'required|integer',
        'amount'           => 'required|numeric|greater_than[0]',
        'commission_rate'  => 'required|numeric|greater_than[0]|less_than_equal_to[100]',
        'commission_amount' => 'required|numeric|greater_than_equal_to[0]',
        'payment_method'   => 'required|in_list[cash,card,online]',
        'earning_date'     => 'required|valid_date',
    ];

    // Get barber earnings by date range
    public function getBarberEarnings($barberId, $startDate = null, $endDate = null)
    {
        $query = $this->where('barber_id', $barberId);
        
        if ($startDate) {
            $query->where('earning_date >=', $startDate);
        }
        
        if ($endDate) {
            $query->where('earning_date <=', $endDate);
        }
        
        return $query->orderBy('earning_date DESC')
                    ->findAll();
    }

    // Get earnings with details
    public function getEarningsWithDetails($barberId, $startDate = null, $endDate = null)
    {
        $query = $this->select('earnings.*, 
                               u.first_name as customer_first_name, u.last_name as customer_last_name,
                               s.service_name, s.price, s.duration')
                      ->join('appointments a', 'a.appointment_id = earnings.appointment_id')
                      ->join('users u', 'u.user_id = a.customer_id')
                      ->join('services s', 's.service_id = earnings.service_id')
                      ->where('earnings.barber_id', $barberId);
        
        if ($startDate) {
            $query->where('earning_date >=', $startDate);
        }
        
        if ($endDate) {
            $query->where('earning_date <=', $endDate);
        }
        
        return $query->orderBy('earning_date DESC')
                    ->findAll();
    }

    // Calculate total earnings
    public function getTotalEarnings($barberId, $startDate = null, $endDate = null)
    {
        $query = $this->where('barber_id', $barberId);
        
        if ($startDate) {
            $query->where('earning_date >=', $startDate);
        }
        
        if ($endDate) {
            $query->where('earning_date <=', $endDate);
        }
        
        $result = $query->select('SUM(commission_amount) as total_earnings, COUNT(*) as total_appointments')
                        ->first();
        
        return [
            'total_earnings' => $result['total_earnings'] ?? 0,
            'total_appointments' => $result['total_appointments'] ?? 0
        ];
    }

    // Get earnings by service type
    public function getEarningsByService($barberId, $startDate = null, $endDate = null)
    {
        $query = $this->select('s.service_name, 
                               SUM(earnings.commission_amount) as total_earnings,
                               COUNT(*) as appointment_count')
                      ->join('services s', 's.service_id = earnings.service_id')
                      ->where('earnings.barber_id', $barberId);
        
        if ($startDate) {
            $query->where('earning_date >=', $startDate);
        }
        
        if ($endDate) {
            $query->where('earning_date <=', $endDate);
        }
        
        return $query->groupBy('s.service_id')
                    ->orderBy('total_earnings DESC')
                    ->findAll();
    }

    // Get earnings by payment method
    public function getEarningsByPaymentMethod($barberId, $startDate = null, $endDate = null)
    {
        $query = $this->select('payment_method, 
                               SUM(commission_amount) as total_earnings,
                               COUNT(*) as transaction_count')
                      ->where('barber_id', $barberId);
        
        if ($startDate) {
            $query->where('earning_date >=', $startDate);
        }
        
        if ($endDate) {
            $query->where('earning_date <=', $endDate);
        }
        
        return $query->groupBy('payment_method')
                    ->orderBy('total_earnings DESC')
                    ->findAll();
    }

    // Get daily earnings
    public function getDailyEarnings($barberId, $startDate = null, $endDate = null)
    {
        $query = $this->select('earning_date, 
                               SUM(commission_amount) as daily_earnings,
                               COUNT(*) as appointment_count')
                      ->where('barber_id', $barberId);
        
        if ($startDate) {
            $query->where('earning_date >=', $startDate);
        }
        
        if ($endDate) {
            $query->where('earning_date <=', $endDate);
        }
        
        return $query->groupBy('earning_date')
                    ->orderBy('earning_date DESC')
                    ->findAll();
    }

    // Get monthly earnings
    public function getMonthlyEarnings($barberId, $year = null)
    {
        $query = $this->select('YEAR(earning_date) as year, 
                               MONTH(earning_date) as month,
                               SUM(commission_amount) as monthly_earnings,
                               COUNT(*) as appointment_count')
                      ->where('barber_id', $barberId);
        
        if ($year) {
            $query->where('YEAR(earning_date)', $year);
        }
        
        return $query->groupBy('YEAR(earning_date), MONTH(earning_date)')
                    ->orderBy('year DESC, month DESC')
                    ->findAll();
    }

    // Add earning from appointment
    public function addFromAppointment($appointmentId, $commissionRate = 70)
    {
        $appointmentModel = new \App\Models\AppointmentModel();
        $appointment = $appointmentModel->getAppointmentWithDetails($appointmentId);
        
        if (!$appointment) {
            return false;
        }

        $commissionAmount = ($appointment['total_amount'] * $commissionRate) / 100;

        $data = [
            'barber_id'        => $appointment['barber_id'],
            'appointment_id'   => $appointmentId,
            'service_id'       => $appointment['service_id'],
            'amount'           => $appointment['total_amount'],
            'commission_rate'  => $commissionRate,
            'commission_amount' => $commissionAmount,
            'payment_method'   => 'cash', // Default, can be updated
            'payment_status'   => 'completed',
            'earning_date'     => $appointment['appointment_date'],
        ];

        return $this->insert($data);
    }

    // Get earnings statistics
    public function getEarningsStats($barberId, $startDate = null, $endDate = null)
    {
        $total = $this->getTotalEarnings($barberId, $startDate, $endDate);
        $byService = $this->getEarningsByService($barberId, $startDate, $endDate);
        $byPayment = $this->getEarningsByPaymentMethod($barberId, $startDate, $endDate);
        $daily = $this->getDailyEarnings($barberId, $startDate, $endDate);

        return [
            'total_earnings' => $total['total_earnings'],
            'total_appointments' => $total['total_appointments'],
            'by_service' => $byService,
            'by_payment_method' => $byPayment,
            'daily_earnings' => $daily,
            'average_per_appointment' => $total['total_appointments'] > 0 ? 
                $total['total_earnings'] / $total['total_appointments'] : 0
        ];
    }

    // Get sum of earnings in a specific date range (commission amount)
    public function getSumForDateRange($barberId, $startDate, $endDate)
    {
        $row = $this->selectSum('commission_amount')
                    ->where('barber_id', $barberId)
                    ->where('earning_date >=', $startDate)
                    ->where('earning_date <=', $endDate)
                    ->first();

        return isset($row['commission_amount']) && $row['commission_amount'] !== null
            ? (float) $row['commission_amount']
            : 0.0;
    }

    // Get earnings by date (for analytics)
    public function getEarningsByDate($barberId = null, $startDate = null, $endDate = null)
    {
        $query = $this->select('earning_date, SUM(commission_amount) as total_earnings')
                      ->groupBy('earning_date')
                      ->orderBy('earning_date', 'ASC');
        
        if ($barberId) {
            $query->where('barber_id', $barberId);
        }
        
        if ($startDate) {
            $query->where('earning_date >=', $startDate);
        }
        
        if ($endDate) {
            $query->where('earning_date <=', $endDate);
        }
        
        return $query->findAll();
    }

    // Get earnings for export
    public function getEarningsForExport($barberId = null, $startDate = null, $endDate = null)
    {
        $query = $this->select('earnings.earning_date as date, 
                               s.service_name, 
                               earnings.commission_amount as amount, 
                               earnings.payment_method')
                      ->join('services s', 's.service_id = earnings.service_id');
        
        if ($barberId) {
            $query->where('earnings.barber_id', $barberId);
        }
        
        if ($startDate) {
            $query->where('earnings.earning_date >=', $startDate);
        }
        
        if ($endDate) {
            $query->where('earnings.earning_date <=', $endDate);
        }
        
        return $query->orderBy('earnings.earning_date', 'DESC')->findAll();
    }
} 