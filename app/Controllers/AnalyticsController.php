<?php

namespace App\Controllers;

use App\Models\AppointmentModel;
use App\Models\UserModel;
use App\Models\ServiceModel;
use App\Models\HaircutHistoryModel;

class AnalyticsController extends BaseController
{
    protected $appointmentModel;
    protected $userModel;
    protected $serviceModel;
    protected $haircutHistoryModel;

    public function __construct()
    {
        $this->appointmentModel = new AppointmentModel();
        $this->userModel = new UserModel();
        $this->serviceModel = new ServiceModel();
        $this->haircutHistoryModel = new HaircutHistoryModel();
    }

    // Analytics dashboard
    public function index()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Analytics Dashboard'
        ];

        return view('analytics/dashboard', $data);
    }

    // Get analytics data
    public function getData()
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $startDate = $this->request->getPost('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getPost('end_date') ?? date('Y-m-d');
        $barberId = $this->request->getPost('barber_id');

        try {
            $metrics = $this->getMetrics($startDate, $endDate, $barberId);
            $chartData = $this->getChartData($startDate, $endDate, $barberId);
            $tableData = $this->getTableData($startDate, $endDate, $barberId);

            return $this->response->setJSON([
                'success' => true,
                'metrics' => $metrics,
                'chartData' => $chartData,
                'tableData' => $tableData
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => 'Failed to load analytics: ' . $e->getMessage()
            ]);
        }
    }

    // Get key metrics
    private function getMetrics($startDate, $endDate, $barberId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('appointments a')
                     ->select('
                         COUNT(*) as total_appointments,
                         SUM(CASE WHEN a.status = "completed" THEN 1 ELSE 0 END) as completed_appointments,
                         SUM(CASE WHEN a.status = "cancelled" THEN 1 ELSE 0 END) as cancelled_appointments,
                         SUM(CASE WHEN a.status = "completed" THEN a.total_amount ELSE 0 END) as total_revenue
                     ')
                     ->where('a.appointment_date >=', $startDate)
                     ->where('a.appointment_date <=', $endDate);

        if ($barberId) {
            $builder->where('a.barber_id', $barberId);
        }

        $result = $builder->get()->getRowArray();

        return [
            'total_appointments' => (int) $result['total_appointments'],
            'completed_appointments' => (int) $result['completed_appointments'],
            'cancelled_appointments' => (int) $result['cancelled_appointments'],
            'total_revenue' => (float) $result['total_revenue']
        ];
    }

    // Get chart data
    private function getChartData($startDate, $endDate, $barberId = null)
    {
        $db = \Config\Database::connect();
        
        // Appointments over time
        $appointmentsOverTime = $this->getAppointmentsOverTime($startDate, $endDate, $barberId);
        
        // Services distribution
        $servicesDistribution = $this->getServicesDistribution($startDate, $endDate, $barberId);

        return [
            'appointments_over_time' => $appointmentsOverTime,
            'services_distribution' => $servicesDistribution
        ];
    }

    // Get appointments over time
    private function getAppointmentsOverTime($startDate, $endDate, $barberId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('appointments a')
                     ->select('DATE(a.appointment_date) as date, COUNT(*) as count')
                     ->where('a.appointment_date >=', $startDate)
                     ->where('a.appointment_date <=', $endDate)
                     ->groupBy('DATE(a.appointment_date)')
                     ->orderBy('a.appointment_date', 'ASC');

        if ($barberId) {
            $builder->where('a.barber_id', $barberId);
        }

        $results = $builder->get()->getResultArray();

        $labels = [];
        $data = [];

        // Fill in missing dates
        $currentDate = new \DateTime($startDate);
        $endDateTime = new \DateTime($endDate);

        while ($currentDate <= $endDateTime) {
            $dateStr = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('M d');
            
            $found = false;
            foreach ($results as $result) {
                if ($result['date'] === $dateStr) {
                    $data[] = (int) $result['count'];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $data[] = 0;
            }
            
            $currentDate->add(new \DateInterval('P1D'));
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    // Get services distribution
    private function getServicesDistribution($startDate, $endDate, $barberId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('appointments a')
                     ->select('s.service_name, COUNT(*) as count')
            ->join('services s', 's.service_id = a.service_id')
                     ->where('a.appointment_date >=', $startDate)
                     ->where('a.appointment_date <=', $endDate)
                     ->where('a.status', 'completed')
                     ->groupBy('s.service_id')
                     ->orderBy('count', 'DESC');

        if ($barberId) {
            $builder->where('a.barber_id', $barberId);
        }

        $results = $builder->get()->getResultArray();

        $labels = [];
        $data = [];

        foreach ($results as $result) {
            $labels[] = $result['service_name'];
            $data[] = (int) $result['count'];
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    // Get table data
    private function getTableData($startDate, $endDate, $barberId = null)
    {
        return [
            'barber_performance' => $this->getBarberPerformance($startDate, $endDate, $barberId),
            'recent_activity' => $this->getRecentActivity($startDate, $endDate, $barberId),
            'detailed_reports' => $this->getDetailedReports($startDate, $endDate, $barberId)
        ];
    }

    // Get barber performance
    private function getBarberPerformance($startDate, $endDate, $barberId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('appointments a')
                     ->select('
                         u.first_name, u.last_name,
                         COUNT(*) as appointments,
                         SUM(CASE WHEN a.status = "completed" THEN a.total_amount ELSE 0 END) as revenue,
                         AVG(CASE WHEN h.customer_rating IS NOT NULL THEN h.customer_rating ELSE NULL END) as rating
                     ')
                     ->join('users u', 'u.user_id = a.barber_id')
                     ->join('haircut_history h', 'h.appointment_id = a.appointment_id', 'left')
            ->where('a.appointment_date >=', $startDate)
            ->where('a.appointment_date <=', $endDate)
                     ->groupBy('a.barber_id')
                     ->orderBy('appointments', 'DESC');

        if ($barberId) {
            $builder->where('a.barber_id', $barberId);
        }

        $results = $builder->get()->getResultArray();

        $performance = [];
        foreach ($results as $result) {
            $performance[] = [
                'name' => $result['first_name'] . ' ' . $result['last_name'],
                'appointments' => (int) $result['appointments'],
                'revenue' => (float) $result['revenue'],
                'rating' => $result['rating'] ? round($result['rating'], 1) : 'N/A'
            ];
        }

        return $performance;
    }

    // Get recent activity
    private function getRecentActivity($startDate, $endDate, $barberId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('appointments a')
                     ->select('
                         a.status, a.appointment_date, a.appointment_time,
                         u1.first_name as customer_first, u1.last_name as customer_last,
                         u2.first_name as barber_first, u2.last_name as barber_last,
                         s.service_name, a.created_at
                     ')
                     ->join('users u1', 'u1.user_id = a.customer_id')
                     ->join('users u2', 'u2.user_id = a.barber_id')
            ->join('services s', 's.service_id = a.service_id')
            ->where('a.appointment_date >=', $startDate)
            ->where('a.appointment_date <=', $endDate)
                     ->orderBy('a.created_at', 'DESC')
                     ->limit(10);

        if ($barberId) {
            $builder->where('a.barber_id', $barberId);
        }

        $results = $builder->get()->getResultArray();

        $activities = [];
        foreach ($results as $result) {
            $activities[] = [
                'type' => $result['status'],
                'title' => $this->getActivityTitle($result),
                'time' => $this->getTimeAgo($result['created_at'])
            ];
        }

        return $activities;
    }

    // Get detailed reports
    private function getDetailedReports($startDate, $endDate, $barberId = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('appointments a')
                     ->select('
                         a.appointment_date, a.appointment_time, a.status, a.total_amount,
                         u1.first_name as customer_first, u1.last_name as customer_last,
                         u2.first_name as barber_first, u2.last_name as barber_last,
                         s.service_name, s.duration
                     ')
                     ->join('users u1', 'u1.user_id = a.customer_id')
                     ->join('users u2', 'u2.user_id = a.barber_id')
            ->join('services s', 's.service_id = a.service_id')
            ->where('a.appointment_date >=', $startDate)
            ->where('a.appointment_date <=', $endDate)
                     ->orderBy('a.appointment_date', 'DESC')
                     ->orderBy('a.appointment_time', 'DESC');

        if ($barberId) {
            $builder->where('a.barber_id', $barberId);
        }

        $results = $builder->get()->getResultArray();

        $reports = [];
        foreach ($results as $result) {
            $reports[] = [
                'date' => date('M d, Y', strtotime($result['appointment_date'])),
                'barber' => $result['barber_first'] . ' ' . $result['barber_last'],
                'customer' => $result['customer_first'] . ' ' . $result['customer_last'],
                'service' => $result['service_name'],
                'status' => ucfirst($result['status']),
                'amount' => (float) $result['total_amount'],
                'duration' => (int) $result['duration']
            ];
        }

        return $reports;
    }

    // Get activity title
    private function getActivityTitle($result)
    {
        $customerName = $result['customer_first'] . ' ' . $result['customer_last'];
        $serviceName = $result['service_name'];
        $date = date('M d', strtotime($result['appointment_date']));
        $time = date('g:i A', strtotime($result['appointment_time']));

        switch ($result['status']) {
            case 'completed':
                return "Completed {$serviceName} for {$customerName} on {$date} at {$time}";
            case 'cancelled':
                return "Cancelled appointment with {$customerName} on {$date} at {$time}";
            case 'confirmed':
                return "Confirmed appointment with {$customerName} for {$date} at {$time}";
            default:
                return "Appointment with {$customerName} on {$date} at {$time}";
        }
    }

    // Get time ago
    private function getTimeAgo($datetime)
    {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'Just now';
        if ($time < 3600) return floor($time / 60) . ' minutes ago';
        if ($time < 86400) return floor($time / 3600) . ' hours ago';
        return floor($time / 86400) . ' days ago';
    }

    // Get barbers for filter
    public function getBarbers()
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['error' => 'Unauthorized']);
        }

        $barbers = $this->userModel->where('role', 'barber')
                                  ->where('is_active', 1)
                                  ->findAll();

        return $this->response->setJSON([
            'success' => true,
            'barbers' => $barbers
        ]);
    }

    // Export analytics data
    public function exportAnalytics()
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/login');
        }

        $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');
        $barberId = $this->request->getGet('barber_id');

        // This would implement CSV/Excel export
        // For now, return JSON
        $data = $this->getDetailedReports($startDate, $endDate, $barberId);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
            'export_date' => date('Y-m-d H:i:s')
        ]);
    }
}