<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id', 'title', 'message', 'type', 'related_id', 'is_read'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_id' => 'required|integer',
        'title' => 'required|max_length[255]',
        'message' => 'required',
        'type' => 'required|in_list[booking,cancellation,reminder,reschedule,general]'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be a valid integer'
        ],
        'title' => [
            'required' => 'Title is required',
            'max_length' => 'Title must not exceed 255 characters'
        ],
        'message' => [
            'required' => 'Message is required'
        ],
        'type' => [
            'required' => 'Type is required',
            'in_list' => 'Type must be one of: booking, cancellation, reminder, reschedule, general'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Get user notifications
    public function getUserNotifications($userId, $limit = 50)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    // Get unread notifications count
    public function getUnreadCount($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->countAllResults();
    }

    // Get unread notifications
    public function getUnreadNotifications($userId, $limit = 10)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    // Mark notification as read
    public function markAsRead($notificationId, $userId)
    {
        $notification = $this->find($notificationId);
        
        if (!$notification || $notification['user_id'] != $userId) {
            return false;
        }

        return $this->update($notificationId, ['is_read' => 1]);
    }

    // Mark all notifications as read for user
    public function markAllAsRead($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->set(['is_read' => 1])
                    ->update();
    }

    // Create appointment notification
    public function createAppointmentNotification($appointmentId, $type)
    {
        $appointmentModel = new \App\Models\AppointmentModel();
        $userModel = new \App\Models\UserModel();
        
        $appointment = $appointmentModel->find($appointmentId);
        if (!$appointment) {
            return false;
        }

        $barber = $userModel->find($appointment['barber_id']);
        $customer = $userModel->find($appointment['customer_id']);

        if (!$barber || !$customer) {
            return false;
        }

        $notificationData = [
            'user_id' => $appointment['barber_id'],
            'title' => $this->getNotificationTitle($type),
            'message' => $this->getNotificationMessage($type, $appointment, $customer),
            'type' => $type,
            'related_id' => $appointmentId,
            'is_read' => 0
        ];

        return $this->insert($notificationData);
    }

    // Get notification title based on type
    private function getNotificationTitle($type)
    {
        switch ($type) {
            case 'new_appointment':
                return 'New Appointment Booked';
            case 'appointment_cancelled':
                return 'Appointment Cancelled';
            case 'appointment_reminder':
                return 'Appointment Reminder';
            case 'appointment_rescheduled':
                return 'Appointment Rescheduled';
            default:
                return 'Appointment Update';
        }
    }

    // Get notification message based on type
    private function getNotificationMessage($type, $appointment, $customer)
    {
        $customerName = $customer['first_name'] . ' ' . $customer['last_name'];
        $date = date('M d, Y', strtotime($appointment['appointment_date']));
        $time = date('g:i A', strtotime($appointment['appointment_time']));

        switch ($type) {
            case 'new_appointment':
                return "{$customerName} has booked an appointment for {$date} at {$time}";
            case 'appointment_cancelled':
                return "{$customerName} has cancelled their appointment for {$date} at {$time}";
            case 'appointment_reminder':
                return "Reminder: You have an appointment with {$customerName} tomorrow at {$time}";
            case 'appointment_rescheduled':
                return "{$customerName} has rescheduled their appointment to {$date} at {$time}";
            default:
                return "Appointment update for {$customerName} on {$date}";
        }
    }

    // Get notifications by type
    public function getNotificationsByType($userId, $type, $limit = 20)
    {
        return $this->where('user_id', $userId)
                    ->where('type', $type)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    // Delete old notifications (cleanup)
    public function deleteOldNotifications($days = 30)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $this->where('created_at <', $cutoffDate)
                    ->where('is_read', 1)
                    ->delete();
    }

    // Get notification statistics
    public function getNotificationStats($userId)
    {
        $total = $this->where('user_id', $userId)->countAllResults();
        $unread = $this->where('user_id', $userId)->where('is_read', 0)->countAllResults();
        $read = $total - $unread;

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $read
        ];
    }
}