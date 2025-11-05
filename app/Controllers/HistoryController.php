<?php

namespace App\Controllers;

use App\Models\HaircutHistoryModel;
use App\Models\HaircutHistoryServiceModel;
use App\Models\AppointmentModel;
use App\Models\UserModel;
use App\Models\ServiceModel;

class HistoryController extends BaseController
{
    protected $haircutHistoryModel;
    protected $appointmentModel;
    protected $userModel;
    protected $serviceModel;
    protected $historyServiceModel;

    public function __construct()
    {
        $this->haircutHistoryModel = new HaircutHistoryModel();
        $this->appointmentModel = new AppointmentModel();
        $this->userModel = new UserModel();
        $this->serviceModel = new ServiceModel();
        $this->historyServiceModel = new HaircutHistoryServiceModel();
    }

    public function index()
    {
        // Check if user is logged in
        if (!session()->get('user_id')) {
            return redirect()->to('/login');
        }

        $user_id = session()->get('user_id');
        $user_role = session()->get('role');

        $data = [
            'title' => 'Haircut History',
            'user' => $this->userModel->find($user_id)
        ];

        // Get history based on user role
        if ($user_role === 'barber') {
            // Barber sees history of haircuts they performed
            $data['history'] = $this->haircutHistoryModel->getBarberHistory($user_id);
            $data['total_appointments'] = $this->appointmentModel->getBarberAppointmentCount($user_id);
            $data['total_earnings'] = $this->appointmentModel->getBarberTotalEarnings($user_id);
            $data['average_rating'] = $this->haircutHistoryModel->getAverageRating($user_id);
            $data['total_customers'] = $this->haircutHistoryModel->getUniqueCustomerCount($user_id);
        } else {
            // Customer sees their own haircut history
            $data['history'] = $this->haircutHistoryModel->getCustomerHistory($user_id);
            $data['total_appointments'] = $this->appointmentModel->getCustomerAppointmentCount($user_id);
            $data['total_spent'] = $this->appointmentModel->getCustomerTotalSpent($user_id);
            $data['total_customers'] = 1; // Customer only sees their own data
        }

        return view('history/index', $data);
    }

    public function view($history_id)
    {
        if (!session()->get('user_id')) {
            return redirect()->to('/login');
        }

        $user_id = session()->get('user_id');
        $user_role = session()->get('role');

        $history = $this->haircutHistoryModel->find($history_id);
        
        if (!$history) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['error' => 'History record not found'])->setStatusCode(404);
            }
            return redirect()->to('/history')->with('error', 'History record not found.');
        }

        // Check if user has permission to view this history
        if ($user_role === 'customer' && $history['customer_id'] != $user_id) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
            }
            return redirect()->to('/history')->with('error', 'Access denied.');
        }

        if ($user_role === 'barber' && $history['barber_id'] != $user_id) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
            }
            return redirect()->to('/history')->with('error', 'Access denied.');
        }

        // Prefer pivot; fallback to JSON for backward compatibility
        $serviceIds = $this->historyServiceModel->getServiceIdsForHistory((int) $history_id);
        if (empty($serviceIds)) {
            $serviceIds = json_decode($history['services_used'] ?? '[]', true) ?: [];
            // Backfill pivot from JSON if available
            if (!empty($serviceIds)) {
                $this->historyServiceModel->setServicesForHistory((int) $history_id, $serviceIds);
            }
        }

        $data = [
            'title' => 'Haircut Details',
            'history' => $history,
            'customer' => $this->userModel->find($history['customer_id']),
            'barber' => $this->userModel->find($history['barber_id']),
            'appointment' => $history['appointment_id'] ? $this->appointmentModel->find($history['appointment_id']) : null,
            'services' => $this->serviceModel->getServicesByIds($serviceIds)
        ];

        // If AJAX request, return just the modal content
        if ($this->request->isAJAX()) {
            return $this->response->setBody($this->getModalContent($data));
        }

        return view('history/view', $data);
    }

    private function getModalContent($data)
    {
        $history = $data['history'];
        $customer = $data['customer'];
        $barber = $data['barber'];
        $services = $data['services'];
        $appointment = $data['appointment'];
        
        $html = '<div class="row">';
        $html .= '<div class="col-md-6">';
        $html .= '<h6 class="text-muted">Date & Time</h6>';
        $html .= '<p class="mb-3"><i class="fas fa-calendar"></i> ' . date('M d, Y', strtotime($history['haircut_date'])) . '<br>';
        $html .= '<small class="text-muted">' . date('h:i A', strtotime($history['created_at'])) . '</small></p>';
        
        $html .= '<h6 class="text-muted">Style Notes</h6>';
        $html .= '<p class="mb-3">' . ($history['style_notes'] ? htmlspecialchars($history['style_notes']) : '<em class="text-muted">No notes provided</em>') . '</p>';
        
        $html .= '<h6 class="text-muted">Total Cost</h6>';
        $html .= '<p class="mb-3"><span class="badge bg-success fs-6">₱' . number_format($history['total_cost'], 2) . '</span></p>';
        $html .= '</div>';
        
        $html .= '<div class="col-md-6">';
        $html .= '<h6 class="text-muted">Customer Rating</h6>';
        if ($history['customer_rating']) {
            $html .= '<div class="mb-3">';
            for ($i = 1; $i <= 5; $i++) {
                $html .= '<i class="fas fa-star ' . ($i <= $history['customer_rating'] ? 'text-warning' : 'text-muted') . '"></i>';
            }
            $html .= '<span class="ms-2">' . $history['customer_rating'] . '/5</span></div>';
        } else {
            $html .= '<p class="mb-3 text-muted">No rating provided</p>';
        }
        
        $html .= '<h6 class="text-muted">Customer Feedback</h6>';
        $html .= '<p class="mb-3">' . ($history['customer_feedback'] ? htmlspecialchars($history['customer_feedback']) : '<em class="text-muted">No feedback provided</em>') . '</p>';
        $html .= '</div></div>';
        
        if (!empty($services)) {
            $html .= '<hr><h6 class="text-muted"><i class="fas fa-list"></i> Services Used</h6>';
            $html .= '<div class="row">';
            foreach ($services as $service) {
                $html .= '<div class="col-md-6 mb-2">';
                $html .= '<div class="d-flex justify-content-between align-items-center p-2 border rounded">';
                $html .= '<div><strong>' . htmlspecialchars($service['service_name']) . '</strong><br>';
                $html .= '<small class="text-muted">' . htmlspecialchars($service['description']) . '</small></div>';
                $html .= '<span class="badge bg-primary">₱' . number_format($service['price'], 2) . '</span>';
                $html .= '</div></div>';
            }
            $html .= '</div>';
        }
        
        if ($history['top_photo'] || $history['left_side_photo'] || $history['right_side_photo'] || $history['back_photo']) {
            $html .= '<hr><h6 class="text-muted"><i class="fas fa-images"></i> Haircut Photos</h6>';
            $html .= '<div class="row">';
            if ($history['top_photo']) {
            	$html .= '<div class="col-md-6 mb-2">'
            	       . '<img src="' . base_url('file/writable?path=' . $history['top_photo']) . '" class="img-fluid rounded mb-1" style="max-height: 120px;">'
            	       . (!empty($history['top_description']) ? '<div><small class="text-muted"><strong>Description:</strong> ' . htmlspecialchars($history['top_description']) . '</small></div>' : '')
            	       . '</div>';
            }
            if ($history['left_side_photo']) {
            	$html .= '<div class="col-md-6 mb-2">'
            	       . '<img src="' . base_url('file/writable?path=' . $history['left_side_photo']) . '" class="img-fluid rounded mb-1" style="max-height: 120px;">'
            	       . (!empty($history['left_side_description']) ? '<div><small class="text-muted"><strong>Description:</strong> ' . htmlspecialchars($history['left_side_description']) . '</small></div>' : '')
            	       . '</div>';
            }
            if ($history['right_side_photo']) {
            	$html .= '<div class="col-md-6 mb-2">'
            	       . '<img src="' . base_url('file/writable?path=' . $history['right_side_photo']) . '" class="img-fluid rounded mb-1" style="max-height: 120px;">'
            	       . (!empty($history['right_side_description']) ? '<div><small class="text-muted"><strong>Description:</strong> ' . htmlspecialchars($history['right_side_description']) . '</small></div>' : '')
            	       . '</div>';
            }
            if ($history['back_photo']) {
            	$html .= '<div class="col-md-6 mb-2">'
            	       . '<img src="' . base_url('file/writable?path=' . $history['back_photo']) . '" class="img-fluid rounded mb-1" style="max-height: 120px;">'
            	       . (!empty($history['back_description']) ? '<div><small class="text-muted"><strong>Description:</strong> ' . htmlspecialchars($history['back_description']) . '</small></div>' : '')
            	       . '</div>';
            }
            $html .= '</div>';
        }
        
        $html .= '<hr><div class="row"><div class="col-6"><strong>' . (session()->get('role') === 'barber' ? 'Customer' : 'Barber') . ':</strong><br>';
        if (session()->get('role') === 'barber') {
            $html .= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) . '<br>';
            $html .= '<small class="text-muted">' . htmlspecialchars($customer['email']) . '</small>';
        } else {
            $html .= htmlspecialchars($barber['first_name'] . ' ' . $barber['last_name']) . '<br>';
            $html .= '<small class="text-muted">' . htmlspecialchars($barber['email']) . '</small>';
        }
        $html .= '</div>';
        
        if ($appointment) {
            $html .= '<div class="col-6"><strong>Appointment:</strong><br>';
            $html .= date('F d, Y', strtotime($appointment['appointment_date'])) . '<br>';
            $html .= '<small class="text-muted">' . date('h:i A', strtotime($appointment['appointment_time'])) . '</small>';
            $html .= '</div>';
        }
        $html .= '</div>';
        
        return $html;
    }

    public function create()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'barber') {
            return redirect()->to('/login');
        }

        $barber_id = session()->get('user_id');
        
        // Get completed appointments for this barber
        $appointments = $this->appointmentModel->getCompletedAppointmentsByBarber($barber_id);
        
        // Filter out appointments that already have haircut history
        $appointmentsWithHistory = $this->haircutHistoryModel->select('appointment_id')->findAll();
        $existingAppointmentIds = array_column($appointmentsWithHistory, 'appointment_id');
        
        $filteredAppointments = array_filter($appointments, function($appointment) use ($existingAppointmentIds) {
            return !in_array($appointment['appointment_id'], $existingAppointmentIds);
        });
        
        $data = [
            'title' => 'Add Haircut History',
            'appointments' => $filteredAppointments,
            'services' => $this->serviceModel->findAll()
        ];

        return view('history/create', $data);
    }

    public function store()
    {
        if (!session()->get('user_id') || session()->get('role') !== 'barber') {
            return redirect()->to('/login');
        }

        $rules = [
            'appointment_id' => 'required|integer',
            'style_name' => 'required|min_length[3]|max_length[255]',
            'style_notes' => 'permit_empty|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $appointmentId = (int) $this->request->getPost('appointment_id');
        $appointment = $this->appointmentModel->find($appointmentId);
        
        if (!is_array($appointment) || empty($appointment) || ((int) $appointment['barber_id']) !== (int) session()->get('user_id')) {
            return redirect()->back()->with('error', 'Invalid appointment selected.');
        }

        // Check if haircut history already exists for this appointment
        $existingHistory = $this->haircutHistoryModel->where('appointment_id', $appointmentId)->first();
        if ($existingHistory) {
            return redirect()->back()->withInput()->with('error', 'Haircut history already exists for this appointment. You can edit the existing record instead.');
        }

        // Calculate total cost from selected services
        $selectedServices = $this->request->getPost('services_used') ?? [];
        $totalCost = 0;
        
        if (!empty($selectedServices)) {
            $services = $this->serviceModel->whereIn('service_id', $selectedServices)->findAll();
            foreach ($services as $service) {
                $totalCost += (float) $service['price'];
            }
        }

        // Handle photo uploads
        $photos = $this->uploadPanelPhotos();

        $data = [
            'customer_id' => (int) $appointment['customer_id'],
            'barber_id' => (int) $appointment['barber_id'],
            'appointment_id' => (int) $appointment['appointment_id'],
            'haircut_date' => date('Y-m-d'), // Set to current date
            'style_name' => $this->request->getPost('style_name'),
            'style_notes' => $this->request->getPost('style_notes'),
            'services_used' => json_encode($selectedServices),
            'total_cost' => $totalCost,
            'customer_rating' => null, // Will be set by customer
            'customer_feedback' => null, // Will be set by customer
            // Photo uploads
            'top_photo' => $photos['top_photo'] ?? null,
            'top_description' => $this->request->getPost('top_description'),
            'left_side_photo' => $photos['left_side_photo'] ?? null,
            'left_side_description' => $this->request->getPost('left_side_description'),
            'right_side_photo' => $photos['right_side_photo'] ?? null,
            'right_side_description' => $this->request->getPost('right_side_description'),
            'back_photo' => $photos['back_photo'] ?? null,
            'back_description' => $this->request->getPost('back_description'),
        ];

        if ($this->haircutHistoryModel->insert($data)) {
            // Dual-write: also store selected services in pivot
            $insertId = (int) $this->haircutHistoryModel->getInsertID();
            $selectedServices = $this->request->getPost('services_used') ?? [];
            $this->historyServiceModel->setServicesForHistory($insertId, is_array($selectedServices) ? $selectedServices : [$selectedServices]);
            return redirect()->to('/history')->with('success', 'Haircut history added successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to add haircut history.');
        }
    }

    public function edit($history_id)
    {
        if (!session()->get('user_id') || session()->get('role') !== 'barber') {
            return redirect()->to('/login');
        }

        $barber_id = session()->get('user_id');
        $history = $this->haircutHistoryModel->find($history_id);

        if (!$history || $history['barber_id'] != $barber_id) {
            return redirect()->to('/history')->with('error', 'History record not found.');
        }

        $data = [
            'title' => 'Edit Haircut History',
            'history' => $history,
            'services' => $this->serviceModel->findAll()
        ];

        return view('history/edit', $data);
    }

    public function update($history_id = null)
    {
        if (!session()->get('user_id') || session()->get('role') !== 'barber') {
            return redirect()->to('/login');
        }

        $history = $this->haircutHistoryModel->find($history_id);
        if (!$history || $history['barber_id'] != session()->get('user_id')) {
            return redirect()->to('/history')->with('error', 'Haircut history not found.');
        }

        $rules = [
            'style_name' => 'required|min_length[3]|max_length[255]',
            'style_notes' => 'permit_empty|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Calculate total cost from selected services
        $selectedServices = $this->request->getPost('services_used') ?? [];
        $totalCost = 0;
        
        if (!empty($selectedServices)) {
            $services = $this->serviceModel->whereIn('service_id', $selectedServices)->findAll();
            foreach ($services as $service) {
                $totalCost += (float) $service['price'];
            }
        }

        // Handle photo uploads
        $photos = $this->uploadPanelPhotos();
        
        $data = [
            'style_name' => $this->request->getPost('style_name'),
            'style_notes' => $this->request->getPost('style_notes'),
            'services_used' => json_encode($selectedServices),
            'total_cost' => $totalCost,
            'customer_rating' => null, // Will be set by customer
            'customer_feedback' => null, // Will be set by customer
        ];

        // Only update photos if new ones are uploaded
        foreach ($photos as $field => $photo) {
            if ($photo) {
                $data[$field] = $photo;
            }
        }

        // Update descriptions
        $data['top_description'] = $this->request->getPost('top_description');
        $data['left_side_description'] = $this->request->getPost('left_side_description');
        $data['right_side_description'] = $this->request->getPost('right_side_description');
        $data['back_description'] = $this->request->getPost('back_description');

        if ($this->haircutHistoryModel->update($history_id, $data)) {
            // Update services in pivot table
            $selectedServices = $this->request->getPost('services_used') ?? [];
            $this->historyServiceModel->setServicesForHistory($history_id, is_array($selectedServices) ? $selectedServices : [$selectedServices]);
            return redirect()->to('/history/view/' . $history_id)->with('success', 'Haircut history updated successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update haircut history.');
        }
    }

    public function delete($history_id)
    {
        if (!session()->get('user_id') || session()->get('role') !== 'barber') {
            return redirect()->to('/login');
        }

        $barber_id = session()->get('user_id');
        $history = $this->haircutHistoryModel->find($history_id);

        if (!$history || $history['barber_id'] != $barber_id) {
            return redirect()->to('/history')->with('error', 'History record not found.');
        }

        if ($this->haircutHistoryModel->delete($history_id)) {
            return redirect()->to('/history')->with('success', 'Haircut history deleted successfully.');
        } else {
            return redirect()->to('/history')->with('error', 'Failed to delete haircut history.');
        }
    }

    private function uploadPhoto($field_name)
    {
        $file = $this->request->getFile($field_name);
        
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Ensure the directory exists
            $uploadPath = WRITEPATH . 'uploads/haircuts';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $newName = $file->getRandomName();
            if ($file->move($uploadPath, $newName)) {
                return 'uploads/haircuts/' . $newName;
            }
        }
        
        return null;
    }

    private function uploadPanelPhotos()
    {
        $photos = [];
        $panels = ['top', 'left_side', 'right_side', 'back'];
        
        foreach ($panels as $panel) {
            $photoField = $panel . '_photo';
            $photo = $this->uploadPhoto($photoField);
            if ($photo) {
                $photos[$photoField] = $photo;
            }
        }
        
        return $photos;
    }

    public function api_get_history()
    {
        if (!session()->get('user_id')) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $user_id = session()->get('user_id');
        $user_role = session()->get('role');

        if ($user_role === 'barber') {
            $history = $this->haircutHistoryModel->getBarberHistory($user_id);
        } else {
            $history = $this->haircutHistoryModel->getCustomerHistory($user_id);
        }

        return $this->response->setJSON($history);
    }

    public function rateHaircut($history_id)
    {
        if (!session()->get('user_id') || session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        $customer_id = session()->get('user_id');
        $history = $this->haircutHistoryModel->find($history_id);

        if (!$history || $history['customer_id'] != $customer_id) {
            return redirect()->to('/history')->with('error', 'Haircut history not found.');
        }

        $data = [
            'title' => 'Rate Your Haircut',
            'history' => $history,
            'barber' => $this->userModel->find($history['barber_id'])
        ];

        return view('history/rate', $data);
    }

    public function submitRating($history_id)
    {
        if (!session()->get('user_id') || session()->get('role') !== 'customer') {
            return redirect()->to('/login');
        }

        $customer_id = session()->get('user_id');
        $history = $this->haircutHistoryModel->find($history_id);

        if (!$history || $history['customer_id'] != $customer_id) {
            return redirect()->to('/history')->with('error', 'Haircut history not found.');
        }

        $rules = [
            'customer_rating' => 'required|integer|greater_than[0]|less_than_equal_to[5]',
            'customer_feedback' => 'permit_empty|max_length[1000]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'customer_rating' => (int) $this->request->getPost('customer_rating'),
            'customer_feedback' => $this->request->getPost('customer_feedback')
        ];

        if ($this->haircutHistoryModel->update($history_id, $data)) {
            return redirect()->to('/history/view/' . $history_id)->with('success', 'Thank you for your rating and feedback!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to submit rating.');
        }
    }
}
