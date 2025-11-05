<?php

namespace App\Models;

use CodeIgniter\Model;

class FeedbackModel extends Model
{
    protected $table = 'feedback';
    protected $primaryKey = 'feedback_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'appointment_id',
        'rating',
        'comment'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false; // No updated_at field

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'appointment_id' => 'required|integer',
        'rating' => 'required|integer|greater_than[0]|less_than_equal_to[5]'
    ];

    protected $validationMessages = [
        'rating' => [
            'required' => 'Rating is required.',
            'greater_than' => 'Rating must be at least 1.',
            'less_than_equal_to' => 'Rating cannot exceed 5.'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Helper methods
    public function getFeedbackByAppointment($appointmentId)
    {
        return $this->where('appointment_id', $appointmentId)->first();
    }

    public function getFeedbackByUser($userId, $limit = 20)
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getFeedbackWithDetails($feedbackId)
    {
        $db = \Config\Database::connect();

        return $db->table('feedback f')
            ->select('f.*, u.first_name, u.last_name, a.appointment_date, a.appointment_time, s.service_name')
            ->join('users u', 'u.user_id = f.user_id')
            ->join('appointments a', 'a.appointment_id = f.appointment_id')
            ->join('services s', 's.service_id = a.service_id')
            ->where('f.feedback_id', $feedbackId)
            ->get()
            ->getRowArray();
    }

    public function getAverageRating($barberId = null, $shopId = null)
    {
        $db = \Config\Database::connect();
        
        // Check if feedback table exists
        if (!$db->tableExists('feedback')) {
            return ['average_rating' => 0, 'total_feedback' => 0];
        }
        
        $builder = $this;

        if ($barberId) {
            $builder = $db->table('feedback f')
                ->select('AVG(f.rating) as average_rating, COUNT(f.feedback_id) as total_feedback')
                ->where('f.barber_id', $barberId);
        } elseif ($shopId) {
            $builder = $db->table('feedback f')
                ->select('AVG(f.rating) as average_rating, COUNT(f.feedback_id) as total_feedback')
                ->join('appointments a', 'a.appointment_id = f.appointment_id')
                ->join('services s', 's.service_id = a.service_id')
                ->where('s.shop_id', $shopId);
        } else {
            $builder = $this->select('AVG(rating) as average_rating, COUNT(feedback_id) as total_feedback');
        }

        return $builder->get()->getRowArray();
    }

    public function getRecentFeedback($limit = 10)
    {
        return $this->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    // Recent reviews for a specific barber (joins appointment for barber_id)
    public function getRecentReviewsForBarber($barberId, $limit = 4)
    {
        $db = \Config\Database::connect();
        
        // Check if feedback table exists
        if (!$db->tableExists('feedback')) {
            return [];
        }
        
        return $db->table('feedback f')
            ->select('f.rating, f.comment, f.created_at, u.first_name, u.last_name')
            ->join('users u', 'u.user_id = f.customer_id')
            ->where('f.barber_id', $barberId)
            ->orderBy('f.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function createFeedback($userId, $appointmentId, $rating, $comment = null)
    {
        $data = [
            'user_id' => $userId,
            'appointment_id' => $appointmentId,
            'rating' => $rating,
            'comment' => $comment
        ];

        return $this->insert($data);
    }
}
