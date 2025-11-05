<?php

namespace App\Controllers;

use App\Services\BackgroundJobProcessor;
use App\Controllers\AutomatedAppointmentController;

class CronController extends BaseController
{
    protected $jobProcessor;
    protected $automatedAppointmentController;

    public function __construct()
    {
        $this->jobProcessor = new BackgroundJobProcessor();
        $this->automatedAppointmentController = new AutomatedAppointmentController();
    }

    /**
     * Run all automated tasks (called by cron job)
     * This endpoint should be called every 5-15 minutes
     */
    public function runAutomatedTasks()
    {
        // Verify this is a cron request (optional security measure)
        $cronToken = $this->request->getGet('token');
        $expectedToken = getenv('CRON_TOKEN') ?: 'cleancut_cron_2024';
        
        if ($cronToken !== $expectedToken) {
            return $this->response->setJSON([
                'error' => 'Unauthorized',
                'message' => 'Invalid cron token'
            ])->setStatusCode(401);
        }

        try {
            $startTime = microtime(true);
            
            // Process all background jobs
            $results = $this->jobProcessor->processAllJobs();
            
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds
            
            log_message('info', "Cron job completed in {$executionTime}ms: " . json_encode($results));
            
            return $this->response->setJSON([
                'success' => true,
                'execution_time_ms' => $executionTime,
                'timestamp' => date('Y-m-d H:i:s'),
                'results' => $results,
                'message' => 'Automated tasks completed successfully'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Cron job error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'error' => 'Cron job failed',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ])->setStatusCode(500);
        }
    }

    /**
     * Run appointment-specific automation (called every 15 minutes)
     */
    public function runAppointmentAutomation()
    {
        $cronToken = $this->request->getGet('token');
        $expectedToken = getenv('CRON_TOKEN') ?: 'cleancut_cron_2024';
        
        if ($cronToken !== $expectedToken) {
            return $this->response->setJSON([
                'error' => 'Unauthorized',
                'message' => 'Invalid cron token'
            ])->setStatusCode(401);
        }

        try {
            $results = $this->jobProcessor->processJob('appointments');
            
            return $this->response->setJSON([
                'success' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'results' => $results,
                'message' => 'Appointment automation completed'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Appointment automation error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'error' => 'Appointment automation failed',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ])->setStatusCode(500);
        }
    }

    /**
     * Send appointment reminders (called daily at 6 PM)
     */
    public function sendAppointmentReminders()
    {
        $cronToken = $this->request->getGet('token');
        $expectedToken = getenv('CRON_TOKEN') ?: 'cleancut_cron_2024';
        
        if ($cronToken !== $expectedToken) {
            return $this->response->setJSON([
                'error' => 'Unauthorized',
                'message' => 'Invalid cron token'
            ])->setStatusCode(401);
        }

        try {
            $result = $this->automatedAppointmentController->sendAppointmentReminders();
            $data = json_decode($result->getBody(), true);
            
            return $this->response->setJSON([
                'success' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'results' => $data,
                'message' => 'Appointment reminders sent'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Reminder sending error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'error' => 'Reminder sending failed',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ])->setStatusCode(500);
        }
    }

    /**
     * Clean up old data (called daily at 2 AM)
     */
    public function cleanupOldData()
    {
        $cronToken = $this->request->getGet('token');
        $expectedToken = getenv('CRON_TOKEN') ?: 'cleancut_cron_2024';
        
        if ($cronToken !== $expectedToken) {
            return $this->response->setJSON([
                'error' => 'Unauthorized',
                'message' => 'Invalid cron token'
            ])->setStatusCode(401);
        }

        try {
            $results = $this->jobProcessor->processJob('cleanup');
            
            return $this->response->setJSON([
                'success' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'results' => $results,
                'message' => 'Data cleanup completed'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Data cleanup error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'error' => 'Data cleanup failed',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ])->setStatusCode(500);
        }
    }

    /**
     * Process earnings summaries (called daily at 11 PM)
     */
    public function processEarningsSummaries()
    {
        $cronToken = $this->request->getGet('token');
        $expectedToken = getenv('CRON_TOKEN') ?: 'cleancut_cron_2024';
        
        if ($cronToken !== $expectedToken) {
            return $this->response->setJSON([
                'error' => 'Unauthorized',
                'message' => 'Invalid cron token'
            ])->setStatusCode(401);
        }

        try {
            $results = $this->jobProcessor->processJob('earnings');
            
            return $this->response->setJSON([
                'success' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'results' => $results,
                'message' => 'Earnings summaries processed'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Earnings processing error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'error' => 'Earnings processing failed',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ])->setStatusCode(500);
        }
    }

    /**
     * Test endpoint to verify cron functionality
     */
    public function test()
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Cron controller is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'server_time' => time(),
            'timezone' => date_default_timezone_get()
        ]);
    }

    /**
     * Get cron job status and statistics
     */
    public function status()
    {
        try {
            $db = \Config\Database::connect();
            
            // Get recent cron job logs
            $recentLogs = $db->table('logs')
                ->where('level', 'info')
                ->like('message', 'Cron job completed')
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();

            // Get system statistics
            $stats = [
                'total_appointments' => $db->table('appointments')->countAllResults(),
                'pending_appointments' => $db->table('appointments')->where('status', 'pending')->countAllResults(),
                'confirmed_appointments' => $db->table('appointments')->where('status', 'confirmed')->countAllResults(),
                'completed_appointments' => $db->table('appointments')->where('status', 'completed')->countAllResults(),
                'unread_notifications' => $db->table('notifications')->where('is_read', 0)->countAllResults(),
                'total_users' => $db->table('users')->where('is_active', 1)->countAllResults(),
            ];

            return $this->response->setJSON([
                'success' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'stats' => $stats,
                'recent_logs' => $recentLogs,
                'message' => 'System status retrieved'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Status check error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'error' => 'Status check failed',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ])->setStatusCode(500);
        }
    }
}
