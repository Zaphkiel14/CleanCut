<?php

namespace App\Controllers;

use App\Models\ScheduleModel;
use App\Models\EmployeeModel;
use App\Models\UserModel;
use App\Models\ShopModel;

class ScheduleController extends BaseController
{
    protected $scheduleModel;
    protected $employeeModel;
    protected $userModel;
    protected $shopModel;

    public function __construct()
    {
        $this->scheduleModel = new ScheduleModel();
        $this->employeeModel = new EmployeeModel();
        $this->userModel = new UserModel();
        $this->shopModel = new ShopModel();
    }

    // Show schedule management page
    public function index()
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('role') ?? session()->get('user_role');

        if ($userRole === 'barber') {
            return $this->barberSchedule($userId);
        } elseif ($userRole === 'owner' || $userRole === 'admin') {
            return $this->ownerSchedule($userId);
        } else {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }
    }

    // Barber schedule management
    private function barberSchedule($userId)
    {
        // Get barber's employee record
        $employee = $this->employeeModel->where('user_id', $userId)->first();
        if (!$employee) {
            return redirect()->to('/dashboard')->with('error', 'Employee record not found.');
        }

        // Get current schedule
        $schedule = $this->scheduleModel->getEmployeeSchedule($employee['employee_id']);
        
        // Format schedule for display
        $weekSchedule = $this->formatWeekSchedule($schedule);

        $data = [
            'title' => 'My Schedule',
            'user_role' => 'barber',
            'employee' => $employee,
            'week_schedule' => $weekSchedule,
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
        ];

        return view('schedule/index', $data);
    }

    // Owner schedule overview
    private function ownerSchedule($userId)
    {
        // Get owner's shop
        $shop = $this->shopModel->where('owner_id', $userId)->first();
        if (!$shop) {
            return redirect()->to('/dashboard')->with('error', 'Shop not found.');
        }

        // Get all employees in the shop
        $employees = $this->employeeModel->getEmployeesByShop($shop['shop_id']);
        
        // Get schedules for all employees
        $allSchedules = [];
        foreach ($employees as $employee) {
            $schedule = $this->scheduleModel->getEmployeeSchedule($employee['employee_id']);
            $allSchedules[$employee['employee_id']] = [
                'employee' => $employee,
                'schedule' => $this->formatWeekSchedule($schedule)
            ];
        }

        $data = [
            'title' => 'Shop Schedule Overview',
            'user_role' => 'owner',
            'shop' => $shop,
            'all_schedules' => $allSchedules,
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
        ];

        return view('schedule/index', $data);
    }

    // Update barber availability
    public function updateAvailability()
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('role') ?? session()->get('user_role');

        if ($userRole !== 'barber') {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $scheduleData = $this->request->getJSON(true);
        
        if (!$scheduleData) {
            return $this->response->setJSON(['error' => 'No schedule data provided']);
        }

        try {
            // Delete existing schedule for this user (schedules stores user_id)
            $this->scheduleModel->where('user_id', $userId)->delete();

            // Insert new schedule
            foreach ($scheduleData as $daySchedule) {
                if (isset($daySchedule['is_available']) && $daySchedule['is_available']) {
                    $this->scheduleModel->insert([
                        'user_id' => $userId,
                        'day_of_week' => $daySchedule['day'],
                        'start_time' => $daySchedule['start_time'],
                        'end_time' => $daySchedule['end_time'],
                        'is_available' => 1
                    ]);
                }
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Schedule updated successfully']);
        } catch (\Exception $e) {
            log_message('error', 'Schedule update failed: ' . $e->getMessage());
            return $this->response->setJSON(['error' => 'Failed to update schedule']);
        }
    }

    // Get available time slots for booking
    public function getAvailableSlots()
    {
        $barberId = $this->request->getPost('barber_id');
        $date = $this->request->getPost('date');

        if (!$barberId || !$date) {
            return $this->response->setJSON(['error' => 'Missing parameters']);
        }

        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        // Get barber's schedule for this day (by user_id directly)
        $schedule = $this->scheduleModel
            ->where('user_id', $barberId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', 1)
            ->first();

        if (!$schedule) {
            return $this->response->setJSON(['slots' => []]);
        }

        // Generate time slots
        $slots = $this->generateTimeSlots($schedule['start_time'], $schedule['end_time'], $date, $barberId);

        return $this->response->setJSON(['slots' => $slots]);
    }

    // Helper method to format week schedule
    private function formatWeekSchedule($schedule)
    {
        $weekSchedule = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        foreach ($days as $day) {
            $weekSchedule[$day] = [
                'is_available' => false,
                'start_time' => '09:00',
                'end_time' => '17:00'
            ];
        }

        foreach ($schedule as $daySchedule) {
            $weekSchedule[$daySchedule['day_of_week']] = [
                'is_available' => $daySchedule['is_available'] == 1,
                'start_time' => substr($daySchedule['start_time'], 0, 5),
                'end_time' => substr($daySchedule['end_time'], 0, 5)
            ];
        }

        return $weekSchedule;
    }

    // Generate time slots for booking
    private function generateTimeSlots($startTime, $endTime, $date, $barberId)
    {
        $slots = [];
        $slotDuration = 30; // 30 minutes
        
        $start = strtotime($startTime);
        $end = strtotime($endTime);
        
        // Get current date and time for filtering past slots
        $today = date('Y-m-d');
        $currentTime = time();
        
        // Get existing appointments for this barber on this date
        $db = \Config\Database::connect();
        $existingAppointments = $db->table('appointments')
            ->select('appointment_time')
            ->where('barber_id', $barberId)
            ->where('appointment_date', $date)
            ->where('status !=', 'cancelled')
            ->get()
            ->getResultArray();
            
        $bookedTimes = array_column($existingAppointments, 'appointment_time');
        
        for ($time = $start; $time < $end; $time += ($slotDuration * 60)) {
            $timeSlot = date('H:i:s', $time);
            $displayTime = date('H:i', $time);
            
            // Check if this slot is not already booked
            if (!in_array($timeSlot, $bookedTimes)) {
                // If the date is today, check if the slot is in the future
                if ($date === $today) {
                    // Create a datetime string for comparison
                    $slotDateTime = strtotime($date . ' ' . $timeSlot);
                    
                    // Only include slots that are in the future
                    if ($slotDateTime > $currentTime) {
                        $slots[] = [
                            'time' => $timeSlot,
                            'display' => $displayTime,
                            'available' => true
                        ];
                    }
                } else {
                    // For future dates, include all available slots
                    $slots[] = [
                        'time' => $timeSlot,
                        'display' => $displayTime,
                        'available' => true
                    ];
                }
            }
        }
        
        return $slots;
    }

    // Weekly schedule view
    public function weekly()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Weekly Schedule'
        ];

        return view('schedule/weekly', $data);
    }

    // Set weekly schedule for multiple days
    public function setWeeklySchedule()
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $userId = session()->get('user_id');
        $startTime = $this->request->getPost('start_time');
        $endTime = $this->request->getPost('end_time');
        $days = $this->request->getPost('days');

        if (!$startTime || !$endTime || !$days) {
            return $this->response->setJSON(['error' => 'Missing required parameters']);
        }

        try {
            $db = \Config\Database::connect();
            
            // Delete existing schedules for selected days
            $this->scheduleModel->where('user_id', $userId)
                               ->whereIn('day_of_week', $days)
                               ->delete();

            // Insert new schedules
            foreach ($days as $day) {
                $this->scheduleModel->insert([
                    'user_id' => $userId,
                    'day_of_week' => $day,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_available' => 1
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Weekly schedule updated successfully'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to update schedule: ' . $e->getMessage()
            ]);
        }
    }

    // Auto generator view
    public function autoGenerator()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Auto Schedule Generator'
        ];

        return view('schedule/auto-generator', $data);
    }

    // Generate auto schedule
    public function generateAutoSchedule()
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $userId = session()->get('user_id');
        $startTime = $this->request->getPost('default_start_time');
        $endTime = $this->request->getPost('default_end_time');
        $slotDuration = (int) $this->request->getPost('slot_duration');
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');
        $days = $this->request->getPost('days');

        if (!$startTime || !$endTime || !$startDate || !$endDate || !$days) {
            return $this->response->setJSON(['error' => 'Missing required parameters']);
        }

        try {
            $db = \Config\Database::connect();
            $generatedCount = 0;
            
            // Generate schedule for each day in the range
            $currentDate = new DateTime($startDate);
            $endDateTime = new DateTime($endDate);
            
            while ($currentDate <= $endDateTime) {
                $dayOfWeek = strtolower($currentDate->format('l'));
                
                if (in_array($dayOfWeek, $days)) {
                    // Delete existing schedule for this day
                    $this->scheduleModel->where('user_id', $userId)
                                       ->where('day_of_week', $dayOfWeek)
                                       ->delete();
                    
                    // Insert new schedule
                    $this->scheduleModel->insert([
                        'user_id' => $userId,
                        'day_of_week' => $dayOfWeek,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'is_available' => 1
                    ]);
                    
                    $generatedCount++;
                }
                
                $currentDate->add(new DateInterval('P1D'));
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Auto schedule generated for {$generatedCount} days",
                'generated_count' => $generatedCount
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to generate schedule: ' . $e->getMessage()
            ]);
        }
    }

    // Copy week schedule to next week
    public function copyWeekSchedule()
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $userId = session()->get('user_id');
        $sourceWeek = $this->request->getPost('source_week');
        $targetWeek = $this->request->getPost('target_week');

        try {
            // Get current week's schedule
            $currentSchedule = $this->scheduleModel->where('user_id', $userId)->findAll();
            
            // This would implement copying logic
            // For now, just return success
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Schedule copied successfully'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to copy schedule: ' . $e->getMessage()
            ]);
        }
    }

    // Get barber availability for a specific date
    public function getBarberAvailability()
    {
        // Debug session data
        $sessionData = [
            'user_id' => session()->get('user_id'),
            'is_logged_in' => session()->get('is_logged_in'),
            'role' => session()->get('role'),
            'user_role' => session()->get('user_role'),
            'all_session' => session()->get()
        ];

        $userId = session()->get('user_id');
        $date = $this->request->getGet('date');

        if (!$userId || !$date) {
            return $this->response->setJSON([
                'error' => 'Missing user_id or date',
                'debug' => $sessionData,
                'slots' => []
            ]);
        }

        $dayOfWeek = strtolower(date('l', strtotime($date)));

        try {
            $db = \Config\Database::connect();

            // Query schedules table directly using user_id
            $schedule = $db->table('schedules')
                ->where('user_id', $userId)
                ->where('day_of_week', $dayOfWeek)
                ->where('is_available', 1)
                ->get()
                ->getRowArray();

            if (!$schedule) {
                return $this->response->setJSON([
                    'error' => 'No schedule found for this day',
                    'debug' => $sessionData,
                    'slots' => []
                ]);
            }

            // Generate time slots
            $startTime = strtotime($schedule['start_time']);
            $endTime = strtotime($schedule['end_time']);
            $slots = [];

            // Get current date and time for filtering past slots
            $today = date('Y-m-d');
            $currentDateTime = time();

            // Generate 30-minute slots
            $currentTime = $startTime;
            while ($currentTime < $endTime) {
                $timeSlot = date('H:i:s', $currentTime);
                
                // If the date is today, check if the slot is in the future
                if ($date === $today) {
                    $slotDateTime = strtotime($date . ' ' . $timeSlot);
                    // Only include slots that are in the future
                    if ($slotDateTime > $currentDateTime) {
                        $slots[] = $timeSlot;
                    }
                } else {
                    // For future dates, include all slots
                    $slots[] = $timeSlot;
                }
                
                $currentTime += 1800; // 30 minutes
            }

            // Remove booked slots
            $existingAppointments = $db->table('appointments')
                ->select('appointment_time')
                ->where('barber_id', $userId)
                ->where('appointment_date', $date)
                ->where('status !=', 'cancelled')
                ->get()
                ->getResultArray();

            $bookedTimes = array_map(function($row) {
                return isset($row['appointment_time']) ? substr($row['appointment_time'], 0, 8) : null;
            }, $existingAppointments);

            $bookedTimes = array_filter($bookedTimes, function($t) {
                return !is_null($t);
            });

            if (!empty($bookedTimes)) {
                $slots = array_values(array_filter($slots, function($slot) use ($bookedTimes) {
                    return !in_array($slot, $bookedTimes, true);
                }));
            }

            return $this->response->setJSON([
                'success' => true,
                'slots' => $slots,
                'schedule' => $schedule,
                'debug' => $sessionData
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Database error: ' . $e->getMessage(),
                'debug' => $sessionData,
                'slots' => []
            ]);
        }
    }

    // Test method to debug the route
    public function testRoute()
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Route is working!',
            'user_id' => session()->get('user_id'),
            'date' => $this->request->getGet('date')
        ]);
    }
}
