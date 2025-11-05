<?php

namespace App\Models;

use CodeIgniter\Model;

class HaircutHistoryModel extends Model
{
    protected $table      = 'haircut_history';
    protected $primaryKey = 'history_id';

    protected $allowedFields = [
        'customer_id',
        'barber_id',
        'appointment_id',
        'haircut_date',
        'style_name',
        'style_notes',
        'top_photo',
        'top_description',
        'left_side_photo',
        'left_side_description',
        'right_side_photo',
        'right_side_description',
        'back_photo',
        'back_description',
        'services_used',
        'total_cost',
        'customer_rating',
        'customer_feedback',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'customer_id'   => 'required|integer',
        'barber_id'     => 'required|integer',
        'haircut_date'  => 'required|valid_date',
        'total_cost'    => 'required|numeric|greater_than[0]',
    ];

    // Get haircut history by customer
    public function getCustomerHistory($customerId, $limit = 20)
    {
        return $this->select('haircut_history.*, 
                             u2.first_name as barber_first_name, u2.last_name as barber_last_name,
                             CONCAT(u2.first_name, " ", u2.last_name) as barber_name')
                    ->join('users u2', 'u2.user_id = haircut_history.barber_id')
                    ->where('haircut_history.customer_id', $customerId)
                    ->orderBy('haircut_history.created_at DESC')
                    ->limit($limit)
                    ->findAll();
    }

    // Get haircut history by barber
    public function getBarberHistory($barberId, $limit = 20)
    {
        return $this->select('haircut_history.*, 
                             u1.first_name as customer_first_name, u1.last_name as customer_last_name,
                             CONCAT(u1.first_name, " ", u1.last_name) as customer_name')
                    ->join('users u1', 'u1.user_id = haircut_history.customer_id')
                    ->where('haircut_history.barber_id', $barberId)
                    ->orderBy('haircut_history.created_at DESC')
                    ->limit($limit)
                    ->findAll();
    }

    // Get haircut history with details
    public function getHistoryWithDetails($historyId)
    {
        return $this->select('haircut_history.*, 
                             u1.first_name as customer_first_name, u1.last_name as customer_last_name,
                             u2.first_name as barber_first_name, u2.last_name as barber_last_name')
                    ->join('users u1', 'u1.user_id = haircut_history.customer_id')
                    ->join('users u2', 'u2.user_id = haircut_history.barber_id')
                    ->where('history_id', $historyId)
                    ->first();
    }

    // Get recent haircut for customer
    public function getRecentHaircut($customerId)
    {
        return $this->where('customer_id', $customerId)
                    ->orderBy('haircut_date DESC')
                    ->first();
    }

    // Get haircut history by date range
    public function getHistoryByDateRange($customerId, $startDate, $endDate)
    {
        return $this->where('customer_id', $customerId)
                    ->where('haircut_date >=', $startDate)
                    ->where('haircut_date <=', $endDate)
                    ->orderBy('haircut_date DESC')
                    ->findAll();
    }

    // Add haircut history from appointment
    public function addFromAppointment($appointmentId, $data = [])
    {
        $appointmentModel = new \App\Models\AppointmentModel();
        $appointment = $appointmentModel->getAppointmentWithDetails($appointmentId);
        
        if (!$appointment) {
            return false;
        }

        $historyData = [
            'customer_id'   => $appointment['customer_id'],
            'barber_id'     => $appointment['barber_id'],
            'appointment_id' => $appointmentId,
            'haircut_date'  => $appointment['appointment_date'],
            'total_cost'    => $appointment['total_amount'],
            'services_used' => json_encode([$appointment['service_id']]),
        ];

        // Merge with additional data
        $historyData = array_merge($historyData, $data);

        $result = $this->insert($historyData);
        if ($result) {
            // Dual-write to pivot
            $insertId = (int) $this->getInsertID();
            $serviceIds = [];
            if (!empty($historyData['services_used'])) {
                $decoded = json_decode($historyData['services_used'], true);
                if (is_array($decoded)) {
                    $serviceIds = $decoded;
                }
            }
            if (!empty($serviceIds)) {
                $pivot = new \App\Models\HaircutHistoryServiceModel();
                $pivot->setServicesForHistory($insertId, $serviceIds);
            }
        }
        return $result;
    }

    // Get average rating for barber
    public function getBarberAverageRating($barberId)
    {
        $result = $this->select('AVG(customer_rating) as average_rating, COUNT(*) as total_ratings')
                       ->where('barber_id', $barberId)
                       ->where('customer_rating IS NOT NULL')
                       ->first();
        
        return [
            'average_rating' => round($result['average_rating'], 1),
            'total_ratings' => $result['total_ratings']
        ];
    }

    // Get average rating for barber (simple method)
    public function getAverageRating($barberId)
    {
        $result = $this->select('AVG(customer_rating) as average_rating')
                       ->where('barber_id', $barberId)
                       ->where('customer_rating IS NOT NULL')
                       ->first();
        
        return $result ? round($result['average_rating'], 1) : 0;
    }

    // Get customer's favorite styles
    public function getCustomerFavoriteStyles($customerId, $limit = 5)
    {
        return $this->select('style_name, COUNT(*) as frequency')
                    ->where('customer_id', $customerId)
                    ->where('style_name IS NOT NULL')
                    ->groupBy('style_name')
                    ->orderBy('frequency DESC')
                    ->limit($limit)
                    ->findAll();
    }

    // Update haircut history with photos
    public function updateWithPhotos($historyId, $beforePhoto = null, $afterPhoto = null)
    {
        $data = [];
        
        if ($beforePhoto) {
            $data['before_photo'] = $beforePhoto;
        }
        
        if ($afterPhoto) {
            $data['after_photo'] = $afterPhoto;
        }
        
        return $this->update($historyId, $data);
    }

    // Get unique customer count for barber
    public function getUniqueCustomerCount($barberId)
    {
        $result = $this->select('COUNT(DISTINCT customer_id) as unique_customers')
                       ->where('barber_id', $barberId)
                       ->first();
        
        return $result ? (int) $result['unique_customers'] : 0;
    }
} 