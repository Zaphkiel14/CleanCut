<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleModel extends Model
{
    protected $table = 'schedules';
    protected $primaryKey = 'schedule_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id', 'day_of_week', 'start_time', 'end_time', 'is_available'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'user_id' => 'required|integer',
        'day_of_week' => 'required|in_list[monday,tuesday,wednesday,thursday,friday,saturday,sunday]',
        'start_time' => 'required',
        'end_time' => 'required'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID is required',
            'integer' => 'User ID must be a valid integer'
        ],
        'day_of_week' => [
            'required' => 'Day of week is required',
            'in_list' => 'Day of week must be a valid day'
        ],
        'start_time' => [
            'required' => 'Start time is required'
        ],
        'end_time' => [
            'required' => 'End time is required'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Get user's schedule
    public function getUserSchedule($userId)
    {
        $db = \Config\Database::connect();
        
        return $db->table('schedules s')
                 ->select('s.*, e.employee_id')
                 ->join('employees e', 'e.user_id = s.user_id')
                 ->where('e.user_id', $userId)
                 ->where('s.is_available', 1)
                 ->orderBy('s.day_of_week', 'ASC')
                 ->get()
                 ->getResultArray();
    }

    // Get employee schedule by employee ID
    public function getEmployeeSchedule($employeeId)
    {
        $db = \Config\Database::connect();
        
        // Get user_id from employee_id first
        $employee = $db->table('employees')
                      ->where('employee_id', $employeeId)
                      ->get()
                      ->getRowArray();
        
        if (!$employee) {
            return [];
        }
        
        // Then get schedule using user_id
        return $this->where('user_id', $employee['user_id'])
                    ->where('is_available', 1)
                    ->orderBy('day_of_week', 'ASC')
                    ->findAll();
    }

    // Get schedule for a specific day
    public function getScheduleByDay($dayOfWeek)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('schedules s')
                     ->select('s.*, e.user_id, u.first_name, u.last_name')
                     ->join('employees e', 'e.user_id = s.user_id')
                     ->join('users u', 'u.user_id = e.user_id')
                     ->where('s.day_of_week', $dayOfWeek)
                     ->where('s.is_available', 1);
        
        return $builder->get()->getResultArray();
    }

    // Check if user is available at a specific time
    public function isUserAvailable($userId, $dayOfWeek, $time)
    {
        $schedule = $this->where('user_id', $userId)
                         ->where('day_of_week', $dayOfWeek)
                         ->where('is_available', 1)
                         ->first();
        
        if (!$schedule) {
            return false;
        }
        
        $startTime = strtotime($schedule['start_time']);
        $endTime = strtotime($schedule['end_time']);
        $requestedTime = strtotime($time);
        
        return ($requestedTime >= $startTime && $requestedTime <= $endTime);
    }

    // Get available time slots for a specific date
    public function getAvailableTimeSlots($userId, $date)
    {
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        $schedule = $this->where('user_id', $userId)
                         ->where('day_of_week', $dayOfWeek)
                         ->where('is_available', 1)
                         ->first();
        
        if (!$schedule) {
            return [];
        }
        
        $startTime = strtotime($schedule['start_time']);
        $endTime = strtotime($schedule['end_time']);
        
        // Get current date and time for filtering past slots
        $today = date('Y-m-d');
        $currentDateTime = time();
        
        // Generate 30-minute time slots
        $slots = [];
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
            
            $currentTime += 1800; // 30 minutes in seconds
        }
        
        return $slots;
    }

    // Get schedule by user ID and day
    public function getScheduleByUserAndDay($userId, $dayOfWeek)
    {
        return $this->where('user_id', $userId)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('is_available', 1)
                    ->first();
    }

    // Update user availability
    public function updateUserAvailability($userId, $dayOfWeek, $startTime, $endTime, $isAvailable = true)
    {
        $data = [
            'user_id' => $userId,
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_available' => $isAvailable ? 1 : 0
        ];
        
        // Check if schedule already exists
        $existing = $this->where('user_id', $userId)
                         ->where('day_of_week', $dayOfWeek)
                         ->first();
        
        if ($existing) {
            return $this->update($existing['schedule_id'], $data);
        } else {
            return $this->insert($data);
        }
    }
} 