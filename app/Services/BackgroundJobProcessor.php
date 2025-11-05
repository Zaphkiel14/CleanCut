<?php

namespace App\Services;

use App\Models\AppointmentModel;
use App\Models\NotificationModel;
use App\Models\EarningsModel;
use App\Models\CommissionSettingsModel;
use App\Models\EmployeeModel;

class BackgroundJobProcessor
{
    protected $appointmentModel;
    protected $notificationModel;
    protected $earningsModel;
    protected $commissionSettingsModel;
    protected $employeeModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->notificationModel = new NotificationModel();
        $this->earningsModel = new EarningsModel();
        $this->commissionSettingsModel = new CommissionSettingsModel();
        $this->employeeModel = new EmployeeModel();
    }

    /**
     * Process all background jobs
     */
    public function processAllJobs()
    {
        $results = [];
        
        try {
            // Process appointment automation
            $results['appointments'] = $this->processAppointmentJobs();
            
            // Process notification cleanup
            $results['notifications'] = $this->processNotificationCleanup();
            
            // Process earnings calculation
            $results['earnings'] = $this->processEarningsJobs();
            
            // Process data cleanup
            $results['cleanup'] = $this->processDataCleanup();
            
            log_message('info', 'Background jobs processed successfully: ' . json_encode($results));
            
            return $results;
            
        } catch (\Exception $e) {
            log_message('error', 'Background job processing error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Process appointment-related automation jobs
     */
    private function processAppointmentJobs()
    {
        $results = [];
        
        try {
            $db = \Config\Database::connect();
            
            // 1. Auto-confirm pending appointments
            $pendingAppointments = $db->table('appointments a')
                ->select('a.*, s.duration, sh.business_hours_start, sh.business_hours_end')
                ->join('services s', 's.service_id = a.service_id')
                ->join('employees e', 'e.user_id = a.barber_id')
                ->join('shops sh', 'sh.shop_id = e.shop_id')
                ->where('a.status', 'pending')
                ->where('a.appointment_date >=', date('Y-m-d'))
                ->where('a.created_at <=', date('Y-m-d H:i:s', strtotime('-30 minutes')))
                ->get()
                ->getResultArray();

            $confirmedCount = 0;
            foreach ($pendingAppointments as $appointment) {
                $appointmentTime = strtotime($appointment['appointment_time']);
                $businessStart = strtotime($appointment['business_hours_start'] ?? '09:00');
                $businessEnd = strtotime($appointment['business_hours_end'] ?? '18:00');
                
                if ($appointmentTime >= $businessStart && $appointmentTime <= $businessEnd) {
                    $this->appointmentModel->update($appointment['appointment_id'], [
                        'status' => 'confirmed',
                        'notes' => 'Auto-confirmed by system',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    $this->notificationModel->createAppointmentNotification(
                        $appointment['appointment_id'], 
                        'appointment_confirmed'
                    );
                    
                    $confirmedCount++;
                }
            }
            $results['auto_confirmed'] = $confirmedCount;

            // 2. Auto-complete appointments past service time
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
                $serviceEndTime = $appointmentDateTime + ($appointment['duration'] * 60);
                
                if ($currentTime > ($serviceEndTime + 900)) { // 15 minutes grace period
                    $this->appointmentModel->update($appointment['appointment_id'], [
                        'status' => 'completed',
                        'notes' => 'Auto-completed - service time completed',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    $this->createEarningsRecord($appointment['appointment_id']);
                    $this->notificationModel->createAppointmentNotification(
                        $appointment['appointment_id'], 
                        'appointment_completed'
                    );
                    
                    $completedCount++;
                }
            }
            $results['auto_completed'] = $completedCount;

            // 3. Auto-cancel no-shows
            $noShowAppointments = $db->table('appointments')
                ->where('status', 'confirmed')
                ->where('appointment_date', date('Y-m-d'))
                ->get()
                ->getResultArray();

            $cancelledCount = 0;
            foreach ($noShowAppointments as $appointment) {
                $appointmentDateTime = strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
                $noShowTime = $appointmentDateTime + 1800; // 30 minutes after appointment time
                
                if ($currentTime > $noShowTime) {
                    $this->appointmentModel->update($appointment['appointment_id'], [
                        'status' => 'cancelled',
                        'notes' => 'Auto-cancelled - no show',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    $this->notificationModel->createAppointmentNotification(
                        $appointment['appointment_id'], 
                        'appointment_cancelled'
                    );
                    
                    $cancelledCount++;
                }
            }
            $results['auto_cancelled'] = $cancelledCount;

            // 4. Send appointment reminders
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
                $existingReminder = $db->table('notifications')
                    ->where('user_id', $appointment['customer_id'])
                    ->where('type', 'appointment_reminder')
                    ->like('data', '"appointment_id":' . $appointment['appointment_id'])
                    ->where('created_at >=', date('Y-m-d 00:00:00'))
                    ->countAllResults();

                if ($existingReminder == 0) {
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
                    
                    $remindersSent++;
                }
            }
            $results['reminders_sent'] = $remindersSent;

        } catch (\Exception $e) {
            log_message('error', 'Appointment job processing error: ' . $e->getMessage());
            $results['error'] = $e->getMessage();
        }

        return $results;
    }

    /**
     * Process notification cleanup jobs
     */
    private function processNotificationCleanup()
    {
        try {
            $deletedCount = $this->notificationModel->cleanupOldNotifications(30);
            return ['deleted_notifications' => $deletedCount];
        } catch (\Exception $e) {
            log_message('error', 'Notification cleanup error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Process earnings-related jobs
     */
    private function processEarningsJobs()
    {
        try {
            $db = \Config\Database::connect();
            
            // Calculate daily earnings summary
            $today = date('Y-m-d');
            $earnings = $db->table('earnings')
                ->select('barber_id, SUM(commission_amount) as total_commission, COUNT(*) as appointment_count')
                ->where('earning_date', $today)
                ->groupBy('barber_id')
                ->get()
                ->getResultArray();

            $results = [];
            foreach ($earnings as $earning) {
                // Create daily summary notification for barbers
                $this->notificationModel->createNotification(
                    $earning['barber_id'],
                    'daily_earnings_summary',
                    'Daily Earnings Summary',
                    "Today you earned ₱" . number_format($earning['total_commission'], 2) . 
                    " from {$earning['appointment_count']} appointments.",
                    [
                        'total_commission' => $earning['total_commission'],
                        'appointment_count' => $earning['appointment_count'],
                        'date' => $today
                    ]
                );
                
                $results[] = [
                    'barber_id' => $earning['barber_id'],
                    'total_commission' => $earning['total_commission'],
                    'appointment_count' => $earning['appointment_count']
                ];
            }

            return ['daily_summaries' => count($results), 'details' => $results];

        } catch (\Exception $e) {
            log_message('error', 'Earnings job processing error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Process data cleanup jobs
     */
    private function processDataCleanup()
    {
        try {
            $db = \Config\Database::connect();
            $results = [];

            // Clean up old login logs (older than 90 days)
            $oldLoginLogs = $db->table('login_logs')
                ->where('created_at <', date('Y-m-d H:i:s', strtotime('-90 days')))
                ->delete();
            $results['deleted_login_logs'] = $oldLoginLogs;

            // Clean up old audit trail entries (older than 1 year)
            $oldAuditLogs = $db->table('audit_trail')
                ->where('created_at <', date('Y-m-d H:i:s', strtotime('-365 days')))
                ->delete();
            $results['deleted_audit_logs'] = $oldAuditLogs;

            // Clean up old messages (older than 6 months)
            $oldMessages = $db->table('messages')
                ->where('created_at <', date('Y-m-d H:i:s', strtotime('-180 days')))
                ->delete();
            $results['deleted_messages'] = $oldMessages;

            return $results;

        } catch (\Exception $e) {
            log_message('error', 'Data cleanup error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
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

            $employee = $this->employeeModel->where('user_id', $appointment['barber_id'])->first();
            if (!$employee) {
                return false;
            }

            $commissionSettings = $this->commissionSettingsModel->getShopCommissionSettings($employee['shop_id']);
            $barberCommissionRate = $commissionSettings['barber_commission_rate'] ?? 70;

            $totalAmount = $appointment['total_amount'];
            $barberCommissionAmount = ($totalAmount * $barberCommissionRate) / 100;

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
     * Process specific job type
     */
    public function processJob($jobType)
    {
        switch ($jobType) {
            case 'appointments':
                return $this->processAppointmentJobs();
            case 'notifications':
                return $this->processNotificationCleanup();
            case 'earnings':
                return $this->processEarningsJobs();
            case 'cleanup':
                return $this->processDataCleanup();
            default:
                return ['error' => 'Unknown job type'];
        }
    }
}
