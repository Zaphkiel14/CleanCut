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

class AutomatedAppointmentController extends BaseController
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

    /**
     * Automatically confirm appointments based on business rules
     */
    public function autoConfirmAppointments()
    {
        try {
            $db = \Config\Database::connect();
            
            // Get pending appointments that should be auto-confirmed
            $pendingAppointments = $db->table('appointments a')
                ->select('a.*, s.duration, sh.business_hours_start, sh.business_hours_end')
                ->join('services s', 's.service_id = a.service_id')
                ->join('employees e', 'e.user_id = a.barber_id')
                ->join('shops sh', 'sh.shop_id = e.shop_id')
                ->where('a.status', 'pending')
                ->where('a.appointment_date >=', date('Y-m-d'))
                ->where('a.created_at <=', date('Y-m-d H:i:s', strtotime('-30 minutes'))) // Created at least 30 minutes ago
                ->get()
                ->getResultArray();

            $confirmedCount = 0;
            
            foreach ($pendingAppointments as $appointment) {
                // Check if appointment is within business hours
                $appointmentTime = strtotime($appointment['appointment_time']);
                $businessStart = strtotime($appointment['business_hours_start'] ?? '09:00');
                $businessEnd = strtotime($appointment['business_hours_end'] ?? '18:00');
                
                if ($appointmentTime >= $businessStart && $appointmentTime <= $businessEnd) {
                    // Auto-confirm the appointment
                    $this->appointmentModel->update($appointment['appointment_id'], [
                        'status' => 'confirmed',
                        'notes' => 'Auto-confirmed by system',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    // Create notification
                    $this->notificationModel->createAppointmentNotification(
                        $appointment['appointment_id'], 
                        'appointment_confirmed'
                    );

                    // Send real-time notification via WebSocket
                    $this->sendWebSocketNotification($appointment['customer_id'], [
                        'type' => 'appointment_confirmed',
                        'appointment_id' => $appointment['appointment_id'],
                        'message' => 'Your appointment has been automatically confirmed'
                    ]);

                    $confirmedCount++;
                }
            }

            log_message('info', "Auto-confirmed {$confirmedCount} appointments");
            
            return $this->response->setJSON([
                'success' => true,
                'confirmed_count' => $confirmedCount,
                'message' => "Auto-confirmed {$confirmedCount} appointments"
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Auto-confirm error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Failed to auto-confirm appointments',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Automatically complete appointments that are past their service time
     */
    public function autoCompleteAppointments()
    {
        try {
            $db = \Config\Database::connect();
            
            // Get confirmed appointments that should be auto-completed
            $confirmedAppointments = $db->table('appointments a')
                ->select('a.*, s.duration')
                ->join('services s', 's.service_id = a.service_id')
                ->where('a.status', 'confirmed')
                ->where('a.appointment_date', date('Y-m-d'))
                ->get()
                ->getResultArray();

            $completedCount = 0;
            $currentTime = time();
            
            foreach ($confirmedAppointments as $appointment) {
                $appointmentDateTime = strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
                $serviceEndTime = $appointmentDateTime + ($appointment['duration'] * 60); // duration in minutes
                
                // If current time is past service end time + 15 minutes grace period
                if ($currentTime > ($serviceEndTime + 900)) { // 15 minutes grace period
                    // Auto-complete the appointment
                    $this->appointmentModel->update($appointment['appointment_id'], [
                        'status' => 'completed',
                        'notes' => 'Auto-completed - service time completed',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    // Create earnings record
                    $this->createEarningsRecord($appointment['appointment_id']);

                    // Create notification
                    $this->notificationModel->createAppointmentNotification(
                        $appointment['appointment_id'], 
                        'appointment_completed'
                    );

                    // Send real-time notification
                    $this->sendWebSocketNotification($appointment['customer_id'], [
                        'type' => 'appointment_completed',
                        'appointment_id' => $appointment['appointment_id'],
                        'message' => 'Your appointment has been completed'
                    ]);

                    $this->sendWebSocketNotification($appointment['barber_id'], [
                        'type' => 'appointment_completed',
                        'appointment_id' => $appointment['appointment_id'],
                        'message' => 'Appointment auto-completed'
                    ]);

                    $completedCount++;
                }
            }

            log_message('info', "Auto-completed {$completedCount} appointments");
            
            return $this->response->setJSON([
                'success' => true,
                'completed_count' => $completedCount,
                'message' => "Auto-completed {$completedCount} appointments"
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Auto-complete error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Failed to auto-complete appointments',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Automatically cancel no-show appointments
     */
    public function autoCancelNoShows()
    {
        try {
            $db = \Config\Database::connect();
            
            // Get confirmed appointments that are no-shows (past appointment time + 30 minutes)
            $noShowAppointments = $db->table('appointments a')
                ->select('a.*')
                ->where('a.status', 'confirmed')
                ->where('a.appointment_date', date('Y-m-d'))
                ->get()
                ->getResultArray();

            $cancelledCount = 0;
            $currentTime = time();
            
            foreach ($noShowAppointments as $appointment) {
                $appointmentDateTime = strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
                $noShowTime = $appointmentDateTime + 1800; // 30 minutes after appointment time
                
                // If current time is past no-show time
                if ($currentTime > $noShowTime) {
                    // Auto-cancel the appointment
                    $this->appointmentModel->update($appointment['appointment_id'], [
                        'status' => 'cancelled',
                        'notes' => 'Auto-cancelled - no show',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    // Create notification
                    $this->notificationModel->createAppointmentNotification(
                        $appointment['appointment_id'], 
                        'appointment_cancelled'
                    );

                    // Send real-time notification
                    $this->sendWebSocketNotification($appointment['customer_id'], [
                        'type' => 'appointment_cancelled',
                        'appointment_id' => $appointment['appointment_id'],
                        'message' => 'Your appointment was cancelled due to no show'
                    ]);

                    $this->sendWebSocketNotification($appointment['barber_id'], [
                        'type' => 'appointment_cancelled',
                        'appointment_id' => $appointment['appointment_id'],
                        'message' => 'Appointment cancelled - no show'
                    ]);

                    $cancelledCount++;
                }
            }

            log_message('info', "Auto-cancelled {$cancelledCount} no-show appointments");
            
            return $this->response->setJSON([
                'success' => true,
                'cancelled_count' => $cancelledCount,
                'message' => "Auto-cancelled {$cancelledCount} no-show appointments"
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Auto-cancel error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Failed to auto-cancel appointments',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send appointment reminders
     */
    public function sendAppointmentReminders()
    {
        try {
            $db = \Config\Database::connect();
            
            // Get appointments for tomorrow
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $appointments = $db->table('appointments a')
                ->select('a.*, c.first_name as customer_name, b.first_name as barber_name, s.service_name')
                ->join('users c', 'c.user_id = a.customer_id')
                ->join('users b', 'b.user_id = a.barber_id')
                ->join('services s', 's.service_id = a.service_id')
                ->where('a.appointment_date', $tomorrow)
                ->where('a.status', 'confirmed')
                ->get()
                ->getResultArray();

            $remindersSent = 0;
            
            foreach ($appointments as $appointment) {
                // Check if reminder already sent today
                $existingReminder = $db->table('notifications')
                    ->where('user_id', $appointment['customer_id'])
                    ->where('type', 'appointment_reminder')
                    ->like('data', '"appointment_id":' . $appointment['appointment_id'])
                    ->where('created_at >=', date('Y-m-d 00:00:00'))
                    ->countAllResults();

                if ($existingReminder == 0) {
                    // Send reminder notification
                    $this->notificationModel->createNotification(
                        $appointment['customer_id'],
                        'appointment_reminder',
                        'Appointment Reminder',
                        "Reminder: You have an appointment tomorrow at " . 
                        date('g:i A', strtotime($appointment['appointment_time'])) . 
                        " with {$appointment['barber_name']} for {$appointment['service_name']}.",
                        [
                            'appointment_id' => $appointment['appointment_id'],
                            'appointment_date' => $appointment['appointment_date'],
                            'appointment_time' => $appointment['appointment_time']
                        ]
                    );

                    // Send real-time notification
                    $this->sendWebSocketNotification($appointment['customer_id'], [
                        'type' => 'appointment_reminder',
                        'appointment_id' => $appointment['appointment_id'],
                        'message' => "Reminder: Appointment tomorrow at " . 
                                   date('g:i A', strtotime($appointment['appointment_time']))
                    ]);

                    $remindersSent++;
                }
            }

            log_message('info', "Sent {$remindersSent} appointment reminders");
            
            return $this->response->setJSON([
                'success' => true,
                'reminders_sent' => $remindersSent,
                'message' => "Sent {$remindersSent} appointment reminders"
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Reminder error: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Failed to send reminders',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create earnings record for completed appointment
     */
    private function createEarningsRecord($appointmentId)
    {
        try {
            $appointment = $this->appointmentModel->getAppointmentWithDetails($appointmentId);
            if (!$appointment) {
                return false;
            }

            // Get commission settings
            $employee = $this->employeeModel->where('user_id', $appointment['barber_id'])->first();
            if (!$employee) {
                return false;
            }

            $commissionSettings = $this->commissionSettingsModel->getShopCommissionSettings($employee['shop_id']);
            $barberCommissionRate = $commissionSettings['barber_commission_rate'] ?? 70;

            // Calculate commission
            $totalAmount = $appointment['total_amount'];
            $barberCommissionAmount = ($totalAmount * $barberCommissionRate) / 100;

            // Create earnings record
            $earningsData = [
                'barber_id' => $appointment['barber_id'],
                'appointment_id' => $appointmentId,
                'service_id' => $appointment['service_id'],
                'amount' => $totalAmount,
                'commission_rate' => $barberCommissionRate,
                'commission_amount' => $barberCommissionAmount,
                'payment_method' => 'cash',
                'earning_date' => $appointment['appointment_date'],
            ];

            return $this->earningsModel->insert($earningsData);

        } catch (\Exception $e) {
            log_message('error', 'Earnings creation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send WebSocket notification
     */
    private function sendWebSocketNotification($userId, $data)
    {
        try {
            // This would integrate with the WebSocket server
            // For now, we'll log it - in production, you'd send via WebSocket
            log_message('info', "WebSocket notification for user {$userId}: " . json_encode($data));
            
            // In a real implementation, you'd send this to the WebSocket server
            // $this->sendToWebSocketServer($userId, $data);
            
        } catch (\Exception $e) {
            log_message('error', 'WebSocket notification error: ' . $e->getMessage());
        }
    }

    /**
     * Run all automated tasks
     */
    public function runAutomatedTasks()
    {
        $results = [];
        
        // Auto-confirm appointments
        $confirmResult = $this->autoConfirmAppointments();
        $results['auto_confirm'] = json_decode($confirmResult->getBody(), true);
        
        // Auto-complete appointments
        $completeResult = $this->autoCompleteAppointments();
        $results['auto_complete'] = json_decode($completeResult->getBody(), true);
        
        // Auto-cancel no-shows
        $cancelResult = $this->autoCancelNoShows();
        $results['auto_cancel'] = json_decode($cancelResult->getBody(), true);
        
        // Send reminders
        $reminderResult = $this->sendAppointmentReminders();
        $results['reminders'] = json_decode($reminderResult->getBody(), true);
        
        return $this->response->setJSON([
            'success' => true,
            'results' => $results,
            'message' => 'All automated tasks completed'
        ]);
    }
}
