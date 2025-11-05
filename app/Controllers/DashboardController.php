<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\ServiceModel;
use App\Models\UserModel;
use App\Models\MessageModel;
use App\Models\ShopModel;
use App\Models\EmployeeModel;
use App\Models\FeedbackModel;

class DashboardController extends BaseController
{
    protected $appointmentModel;
    protected $serviceModel;
    protected $userModel;
    protected $messageModel;
    protected $shopModel;
    protected $employeeModel;
    protected $feedbackModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->serviceModel = new ServiceModel();
        $this->userModel = new UserModel();
        $this->messageModel = new MessageModel();
        $this->shopModel = new ShopModel();
        $this->employeeModel = new EmployeeModel();
        $this->feedbackModel = new FeedbackModel();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('role') ?? session()->get('user_role'); // Try both session keys

        if (!$userId) {
            return redirect()->to('login');
        }

        // Debug: Log the role for troubleshooting
        log_message('info', 'User ID: ' . $userId . ', Role: ' . $userRole);

        $data = [
            'title' => 'Dashboard - CleanCut',
            'user_role' => $userRole
        ];

        // Get dashboard data based on user role
        switch ($userRole) {
            case 'customer':
                $data = array_merge($data, $this->getCustomerDashboard($userId));
                break;
            case 'barber':
                $data = array_merge($data, $this->getBarberDashboard($userId));
                break;
            case 'owner':
                $data = array_merge($data, $this->getOwnerDashboard($userId));
                break;
            case 'admin':
                $data = array_merge($data, $this->getAdminDashboard());
                break;
            default:
                $data = array_merge($data, $this->getDefaultDashboard($userId));
        }

        return view('dashboard/index', $data);
    }

    private function getCustomerDashboard($userId)
    {
        // Get customer's upcoming appointments with service and barber info
        $appointments = $this->appointmentModel
            ->select('appointments.*, s.service_name, u.first_name as barber_name')
            ->join('services s', 's.service_id = appointments.service_id', 'left')
            ->join('users u', 'u.user_id = appointments.barber_id', 'left')
            ->where('appointments.customer_id', $userId)
            ->where('appointments.appointment_date >=', date('Y-m-d'))
            ->where('appointments.status !=', 'cancelled')
            ->orderBy('appointments.appointment_date', 'ASC')
            ->orderBy('appointments.appointment_time', 'ASC')
            ->findAll(5);

        // Get customer's haircut history with service and barber info
        $history = $this->appointmentModel
            ->select('appointments.*, s.service_name, u.first_name as barber_name')
            ->join('services s', 's.service_id = appointments.service_id', 'left')
            ->join('users u', 'u.user_id = appointments.barber_id', 'left')
            ->where('appointments.customer_id', $userId)
            ->where('appointments.status', 'completed')
            ->orderBy('appointments.appointment_date', 'DESC')
            ->findAll(5);

                        return [
                    'appointments' => $appointments,
                    'history' => $history,
                    'total_appointments' => $this->appointmentModel->where('customer_id', $userId)->countAllResults(),
                    'completed_appointments' => $this->appointmentModel->where('customer_id', $userId)->where('status', 'completed')->countAllResults()
                ];
    }

    private function getBarberDashboard($userId)
    {
        // Get barber's today's appointments with customer and service info
        $todayAppointments = $this->appointmentModel
            ->select('appointments.*, c.first_name as customer_name, s.service_name')
            ->join('users c', 'c.user_id = appointments.customer_id', 'left')
            ->join('services s', 's.service_id = appointments.service_id', 'left')
            ->where('appointments.barber_id', $userId)
            ->where('appointments.appointment_date', date('Y-m-d'))
            ->where('appointments.status !=', 'cancelled')
            ->orderBy('appointments.appointment_time', 'ASC')
            ->findAll();

        // Get barber's upcoming appointments
        $upcomingAppointments = $this->appointmentModel
            ->where('barber_id', $userId)
            ->where('appointment_date >', date('Y-m-d'))
            ->where('status !=', 'cancelled')
            ->orderBy('appointment_date', 'ASC')
            ->orderBy('appointment_time', 'ASC')
            ->findAll(5);

        // Get barber's recent completed appointments
        $recentCompleted = $this->appointmentModel
            ->where('barber_id', $userId)
            ->where('status', 'completed')
            ->orderBy('appointment_date', 'DESC')
            ->findAll(5);

        // Get barber's ratings
        $ratings = $this->feedbackModel->getAverageRating($userId);

        return [
            'today_appointments' => $todayAppointments,
            'upcoming_appointments' => $upcomingAppointments,
            'recent_completed' => $recentCompleted,
            'ratings' => $ratings,
            'total_appointments' => $this->appointmentModel->where('barber_id', $userId)->countAllResults(),
            'completed_appointments' => $this->appointmentModel->where('barber_id', $userId)->where('status', 'completed')->countAllResults()
        ];
    }

    private function getAdminDashboard()
    {
        // Get overall statistics
        $totalUsers = $this->userModel->countAllResults();
        $totalServices = $this->serviceModel->countAllResults();
        $totalAppointments = $this->appointmentModel->countAllResults();
        $totalShops = $this->shopModel->countAllResults();

        // Get recent appointments with customer and barber names
        $recentAppointments = $this->appointmentModel
            ->select('a.*, c.first_name as customer_first_name, c.last_name as customer_last_name, b.first_name as barber_first_name, b.last_name as barber_last_name, s.service_name')
            ->from('appointments a')
            ->join('users c', 'c.user_id = a.customer_id', 'left')
            ->join('users b', 'b.user_id = a.barber_id', 'left')
            ->join('services s', 's.service_id = a.service_id', 'left')
            ->orderBy('a.created_at', 'DESC')
            ->findAll(10);

        // Get recent users
        $recentUsers = $this->userModel
            ->orderBy('created_at', 'DESC')
            ->findAll(10);

        // Get shop statistics
        $shopStats = $this->getShopStats();

        return [
            'total_users' => $totalUsers,
            'total_services' => $totalServices,
            'total_appointments' => $totalAppointments,
            'total_shops' => $totalShops,
            'recent_appointments' => $recentAppointments,
            'recent_users' => $recentUsers,
            'shop_stats' => $shopStats
        ];
    }

    private function getOwnerDashboard($userId)
    {
        // Get shop owned by this user
        $shop = $this->shopModel->where('owner_id', $userId)->first();
        
        if (!$shop) {
            // If no shop found, return empty data
            return [
                'total_users' => 0,
                'total_services' => 0,
                'total_appointments' => 0,
                'total_shops' => 0,
                'recent_appointments' => [],
                'recent_users' => [],
                'shop_stats' => [],
                'shop' => null,
                'shop_employees' => [],
                'shop_services' => [],
                'shop_appointments' => []
            ];
        }

        // Get shop employees
        $shopEmployeesRaw = $this->employeeModel
            ->select('e.*, u.first_name, u.last_name, u.email')
            ->from('employees e')
            ->join('users u', 'u.user_id = e.user_id', 'left')
            ->where('e.shop_id', $shop['shop_id'])
            ->findAll();
        // Filter unique employees by user_id
        $seen = [];
        $shopEmployees = [];
        foreach ($shopEmployeesRaw as $employee) {
            if (!in_array($employee['user_id'], $seen)) {
                $seen[] = $employee['user_id'];
                $shopEmployees[] = $employee;
            }
        }

        // Get shop services
        $shopServices = $this->serviceModel
            ->where('shop_id', $shop['shop_id'])
            ->findAll();

        // Get shop appointments
        $shopAppointments = $this->appointmentModel
            ->select('a.*, c.first_name as customer_first_name, c.last_name as customer_last_name, b.first_name as barber_first_name, b.last_name as barber_last_name, s.service_name')
            ->from('appointments a')
            ->join('services s', 's.service_id = a.service_id', 'left')
            ->join('users c', 'c.user_id = a.customer_id', 'left')
            ->join('users b', 'b.user_id = a.barber_id', 'left')
            ->where('s.shop_id', $shop['shop_id'])
            ->orderBy('a.created_at', 'DESC')
            ->findAll(10);

        // Get shop statistics
        $totalShopEmployees = count($shopEmployees);
        $totalShopServices = count($shopServices);
        $totalShopAppointments = $this->appointmentModel
            ->join('services s', 's.service_id = appointments.service_id', 'left')
            ->where('s.shop_id', $shop['shop_id'])
            ->countAllResults();

        // Get recent customers for this shop
        $recentCustomers = $this->userModel
            ->select('u.*')
            ->from('users u')
            ->join('appointments a', 'a.customer_id = u.user_id', 'left')
            ->join('services s', 's.service_id = a.service_id', 'left')
            ->where('s.shop_id', $shop['shop_id'])
            ->groupBy('u.user_id')
            ->orderBy('u.created_at', 'DESC')
            ->findAll(10);

        return [
            'total_users' => count($recentCustomers),
            'total_services' => $totalShopServices,
            'total_appointments' => $totalShopAppointments,
            'total_shops' => 1, // Owner has 1 shop
            'recent_appointments' => $shopAppointments,
            'recent_users' => $recentCustomers,
            'shop_stats' => [],
            'shop' => $shop,
            'shop_employees' => $shopEmployees,
            'shop_services' => $shopServices,
            'shop_appointments' => $shopAppointments
        ];
    }

    private function getDefaultDashboard($userId)
    {
        // Get user's basic information
        $user = $this->userModel->find($userId);

        // Get recent activity
        $recentActivity = $this->appointmentModel
            ->where('customer_id', $userId)
            ->orWhere('barber_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll(5);

        return [
            'user' => $user,
            'recent_activity' => $recentActivity
        ];
    }

    private function getShopStats()
    {
        $db = \Config\Database::connect();

        return $db->table('shops s')
            ->select('s.shop_name, COUNT(DISTINCT e.employee_id) as employees, COUNT(DISTINCT a.appointment_id) as appointments')
            ->join('employees e', 'e.shop_id = s.shop_id', 'left')
            ->join('services sv', 'sv.shop_id = s.shop_id', 'left')
            ->join('appointments a', 'a.service_id = sv.service_id', 'left')
            ->groupBy('s.shop_id')
            ->get()
            ->getResultArray();
    }

    // AJAX: Update barber availability for a specific date
    public function updateAvailability()
    {
        // Debug: Log request details
        log_message('debug', 'Update Availability Request:');
        log_message('debug', 'Method: ' . $this->request->getMethod());
        log_message('debug', 'Headers: ' . json_encode($this->request->getHeaders()));
        log_message('debug', 'Body: ' . $this->request->getBody());

        // Debug: Check request method
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setJSON(['success' => false, 'error' => 'Invalid request method: ' . $this->request->getMethod()]);
        }

        // Debug: Check user session
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['success' => false, 'error' => 'No user_id in session']);
        }

        // Debug: Check JSON data
        $data = $this->request->getJSON(true);
        if (!$data) {
            return $this->response->setJSON(['success' => false, 'error' => 'No JSON data received. Raw body: ' . $this->request->getBody()]);
        }

        // Debug: Check required fields
        $date = $data['date'] ?? null;
        $slots = $data['slots'] ?? [];
        if (!$date || !is_array($slots)) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Missing date or slots in data',
                'received_data' => $data
            ]);
        }

        // Debug: Check date format
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        if (!$dayOfWeek) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Invalid date format',
                'date' => $date,
                'day_of_week' => $dayOfWeek
            ]);
        }

        try {
            $db = \Config\Database::connect();

            // Debug: Check database connection
            if (!$db) {
                return $this->response->setJSON(['success' => false, 'error' => 'Database connection failed']);
            }

            // Check if availability table exists, if not create it
            if (!$db->tableExists('availability')) {
                $this->createAvailabilityTable($db);
            }

            // Start transaction
            $db->transStart();

            // Delete existing availability for this date
            $db->table('availability')
                ->where('user_id', $userId)
                ->where('available_date', $date)
                ->delete();

            // Insert individual time slots if they exist
            if (count($slots) > 0) {
                $insertData = [];
                foreach ($slots as $slot) {
                    $insertData[] = [
                        'user_id' => $userId,
                        'available_date' => $date,
                        'available_time' => $slot,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }

                $result = $db->table('availability')->insertBatch($insertData);

                if (!$result) {
                    $error = $db->error();
                    $db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'error' => 'Insert failed',
                        'db_error' => $error
                    ]);
                }
            }

            // Complete transaction
            $db->transComplete();

            if ($db->transStatus() === false) {
                $errorInfo = $db->error();
                return $this->response->setJSON([
                    'success' => false,
                    'error' => 'Transaction failed',
                    'db_error' => $errorInfo,
                    'message' => isset($errorInfo['message']) ? $errorInfo['message'] : 'Unknown database error'
                ]);
            }

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Exception occurred: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // AJAX: Get barber availability for a specific date
    public function getAvailability()
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['slots' => []]);
        }

        $date = $this->request->getGet('date');
        if (!$date) {
            return $this->response->setJSON(['slots' => []]);
        }

        try {
            $db = \Config\Database::connect();
            
            // Check if table exists, if not return empty
            if (!$db->tableExists('availability')) {
                return $this->response->setJSON(['slots' => []]);
            }
            
            // Get specific availability slots for this date
            $availabilityRecords = $db->table('availability')
                ->where('user_id', $userId)
                ->where('available_date', $date)
                ->orderBy('available_time', 'ASC')
                ->get()
                ->getResultArray();

            $slots = [];
            
            // Get current date and time for filtering past slots
            $today = date('Y-m-d');
            $currentDateTime = time();
            
            foreach ($availabilityRecords as $record) {
                $timeSlot = $record['available_time'];
                
                // If the date is today, check if the slot is in the future
                if ($date === $today) {
                    $slotDateTime = strtotime($date . ' ' . $timeSlot);
                    // Only include slots that are in the future
                    if ($slotDateTime > $currentDateTime) {
                        $slots[] = substr($timeSlot, 0, 5); // Return HH:MM format
                    }
                } else {
                    // For future dates, include all slots
                    $slots[] = substr($timeSlot, 0, 5); // Return HH:MM format
                }
            }

            return $this->response->setJSON(['slots' => $slots]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Exception occurred: ' . $e->getMessage(),
                'slots' => []
            ]);
        }
    }

    // Debug method to test if the route is working
    public function testUpdateAvailability()
    {
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Route is working!',
            'method' => $this->request->getMethod(),
            'user_id' => session()->get('user_id'),
            'data' => $this->request->getJSON(true)
        ]);
    }

    // Create availability table if it doesn't exist
    private function createAvailabilityTable($db)
    {
        $forge = \Config\Database::forge();
        
        $fields = [
            'availability_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'available_date' => [
                'type' => 'DATE',
            ],
            'available_time' => [
                'type' => 'TIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        $forge->addField($fields);
        $forge->addPrimaryKey('availability_id');
        $forge->addKey('user_id');
        $forge->addKey('available_date');
        $forge->addKey(['user_id', 'available_date']);
        $forge->createTable('availability', true);
    }
}
