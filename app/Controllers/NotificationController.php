<?php

namespace App\Controllers;

use App\Models\NotificationModel;
use App\Models\AppointmentModel;
use App\Models\UserModel;

class NotificationController extends BaseController
{
    protected $notificationModel;
    protected $appointmentModel;
    protected $userModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        $this->appointmentModel = new AppointmentModel();
        $this->userModel = new UserModel();
    }

    // Send booking notification
    public function sendBookingNotification($appointmentId, $type = 'booking')
    {
        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            return false;
        }

        $barber = $this->userModel->find($appointment['barber_id']);
        $customer = $this->userModel->find($appointment['customer_id']);

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

        return $this->notificationModel->insert($notificationData);
    }

    // Send email notification
    public function sendEmailNotification($appointmentId, $type = 'booking')
    {
        $appointment = $this->appointmentModel->find($appointmentId);
        if (!$appointment) {
            return false;
        }

        $barber = $this->userModel->find($appointment['barber_id']);
        $customer = $this->userModel->find($appointment['customer_id']);

        if (!$barber || !$customer) {
            return false;
        }

        $email = \Config\Services::email();
        $email->setTo($barber['email']);
        $email->setSubject($this->getEmailSubject($type));
        $email->setMessage($this->getEmailMessage($type, $appointment, $customer, $barber));

        return $email->send();
    }

    // Get notification title
    private function getNotificationTitle($type)
    {
        switch ($type) {
            case 'booking':
                return 'New Appointment Booked';
            case 'cancellation':
                return 'Appointment Cancelled';
            case 'reminder':
                return 'Appointment Reminder';
            case 'reschedule':
                return 'Appointment Rescheduled';
            default:
                return 'Appointment Update';
        }
    }

    // Get notification message
    private function getNotificationMessage($type, $appointment, $customer)
    {
        $customerName = $customer['first_name'] . ' ' . $customer['last_name'];
        $date = date('M d, Y', strtotime($appointment['appointment_date']));
        $time = date('g:i A', strtotime($appointment['appointment_time']));

        switch ($type) {
            case 'booking':
                return "{$customerName} has booked an appointment for {$date} at {$time}";
            case 'cancellation':
                return "{$customerName} has cancelled their appointment for {$date} at {$time}";
            case 'reminder':
                return "Reminder: You have an appointment with {$customerName} tomorrow at {$time}";
            case 'reschedule':
                return "{$customerName} has rescheduled their appointment to {$date} at {$time}";
            default:
                return "Appointment update for {$customerName} on {$date}";
        }
    }

    // Get email subject
    private function getEmailSubject($type)
    {
        switch ($type) {
            case 'booking':
                return 'New Appointment Booked - CleanCut';
            case 'cancellation':
                return 'Appointment Cancelled - CleanCut';
            case 'reminder':
                return 'Appointment Reminder - CleanCut';
            case 'reschedule':
                return 'Appointment Rescheduled - CleanCut';
            default:
                return 'Appointment Update - CleanCut';
        }
    }

    // Get email message
    private function getEmailMessage($type, $appointment, $customer, $barber)
    {
        $customerName = $customer['first_name'] . ' ' . $customer['last_name'];
        $date = date('M d, Y', strtotime($appointment['appointment_date']));
        $time = date('g:i A', strtotime($appointment['appointment_time']));
        $barberName = $barber['first_name'] . ' ' . $barber['last_name'];

        $message = "<h2>CleanCut Appointment Notification</h2>";
        $message .= "<p>Hello {$barberName},</p>";

        switch ($type) {
            case 'booking':
                $message .= "<p><strong>New Appointment Booked</strong></p>";
                $message .= "<p>Customer: {$customerName}</p>";
                $message .= "<p>Date: {$date}</p>";
                $message .= "<p>Time: {$time}</p>";
                $message .= "<p>Please prepare for this appointment.</p>";
                break;
            case 'cancellation':
                $message .= "<p><strong>Appointment Cancelled</strong></p>";
                $message .= "<p>Customer: {$customerName}</p>";
                $message .= "<p>Date: {$date}</p>";
                $message .= "<p>Time: {$time}</p>";
                $message .= "<p>This time slot is now available.</p>";
                break;
            case 'reminder':
                $message .= "<p><strong>Appointment Reminder</strong></p>";
                $message .= "<p>You have an appointment with {$customerName} tomorrow at {$time}</p>";
                $message .= "<p>Please ensure you're prepared.</p>";
                break;
            case 'reschedule':
                $message .= "<p><strong>Appointment Rescheduled</strong></p>";
                $message .= "<p>Customer: {$customerName}</p>";
                $message .= "<p>New Date: {$date}</p>";
                $message .= "<p>New Time: {$time}</p>";
                break;
        }

        $message .= "<p>Thank you for using CleanCut!</p>";
        $message .= "<p>Best regards,<br>CleanCut Team</p>";

        return $message;
    }

    // Get user notifications
    public function index()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/login');
        }

        $userId = session()->get('user_id');
        $notifications = $this->notificationModel->getUserNotifications($userId);

        $data = [
            'title' => 'Notifications',
            'notifications' => $notifications
        ];

        return view('notifications/index', $data);
    }

    // Get unread notifications count
    public function getUnread()
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['count' => 0]);
        }

        $userId = session()->get('user_id');
        $count = $this->notificationModel->getUnreadCount($userId);

        return $this->response->setJSON(['count' => $count]);
    }

    // Mark notification as read
    public function markAsRead($notificationId)
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $userId = session()->get('user_id');
        $notification = $this->notificationModel->find($notificationId);

        if (!$notification || $notification['user_id'] != $userId) {
            return $this->response->setJSON(['error' => 'Notification not found']);
        }

        $this->notificationModel->update($notificationId, ['is_read' => 1]);

            return $this->response->setJSON(['success' => true]);
    }

    // Mark all notifications as read
    public function markAllAsRead()
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $userId = session()->get('user_id');
        $this->notificationModel->where('user_id', $userId)
                               ->where('is_read', 0)
                               ->set(['is_read' => 1])
                               ->update();

        return $this->response->setJSON(['success' => true]);
    }

    // Get all notifications
    public function getAll()
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $userId = session()->get('user_id');
        $notifications = $this->notificationModel->getUserNotifications($userId);

        return $this->response->setJSON([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    // Get notifications by type
    public function getByType($type)
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $userId = session()->get('user_id');
        $notifications = $this->notificationModel->getNotificationsByType($userId, $type);

        return $this->response->setJSON([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    // View notification details
    public function view($notificationId)
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $userId = session()->get('user_id');
        $notification = $this->notificationModel->find($notificationId);

        if (!$notification || $notification['user_id'] != $userId) {
            return $this->response->setJSON(['error' => 'Notification not found']);
        }

        return $this->response->setJSON([
            'success' => true,
            'notification' => $notification
        ]);
    }

    // Get notification statistics
    public function getStats()
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $userId = session()->get('user_id');
        $stats = $this->notificationModel->getNotificationStats($userId);
        
        // Add today's count
        $today = date('Y-m-d');
        $todayCount = $this->notificationModel->where('user_id', $userId)
                                             ->where('DATE(created_at)', $today)
                                             ->countAllResults();
        
        $stats['today'] = $todayCount;

        return $this->response->setJSON([
            'success' => true,
            'stats' => $stats
        ]);
    }

    // Send appointment reminders
    public function sendReminders()
    {
        // Get appointments for tomorrow
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $appointments = $this->appointmentModel->getAppointmentsForDate($tomorrow);

        $sentCount = 0;
        foreach ($appointments as $appointment) {
            // Send in-app notification
            $this->sendBookingNotification($appointment['appointment_id'], 'reminder');
            
            // Send email notification
            $this->sendEmailNotification($appointment['appointment_id'], 'reminder');
            
            $sentCount++;
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Sent {$sentCount} reminders"
        ]);
    }
}