<?php

namespace App\Controllers;

use App\Models\ServiceModel;
use App\Models\ShopModel;
use App\Models\UserModel;
use App\Models\EmployeeModel;

class Services extends BaseController
{
    private ServiceModel $serviceModel;
    private ShopModel $shopModel;
    private UserModel $userModel;
    private EmployeeModel $employeeModel;

    public function __construct()
    {
        $this->serviceModel = new ServiceModel();
        $this->shopModel = new ShopModel();
        $this->userModel = new UserModel();
        $this->employeeModel = new EmployeeModel();
    }

    public function index()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role') ?? session()->get('user_role');

        if (!$userId) {
            return redirect()->to('login');
        }

        $services = [];
        $shop = null;

        if ($role === 'owner') {
            // Owner: show services for their shop only
            $shop = $this->shopModel->where('owner_id', $userId)->first();
            if ($shop) {
                $services = $this->serviceModel->where('shop_id', $shop['shop_id'])->findAll();
            }
        } elseif ($role === 'admin') {
            // Admin: show all services
            $services = $this->serviceModel->orderBy('shop_id', 'ASC')->findAll();
        } else {
            // Other roles: redirect to dashboard
            return redirect()->to('dashboard');
        }

        $data = [
            'title' => 'Service Management',
            'services' => $services,
            'shop' => $shop,
            'user_role' => $role,
        ];

        return view('services/index', $data);
    }

    public function create()
    {
        $role = session()->get('role') ?? session()->get('user_role');
        if ($role !== 'owner' && $role !== 'admin') {
            return redirect()->to('dashboard');
        }

        return view('services/create', [
            'title' => 'Add Service',
            'user_role' => $role,
        ]);
    }

    public function store()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role') ?? session()->get('user_role');
        if (!$userId || ($role !== 'owner' && $role !== 'admin')) {
            return redirect()->to('login');
        }

        $shopId = null;
        if ($role === 'owner') {
            $shop = $this->shopModel->where('owner_id', $userId)->first();
            if (!$shop) {
                return redirect()->back()->with('error', 'You do not have a shop yet.');
            }
            $shopId = (int) $shop['shop_id'];
        } else {
            // admin can specify shop_id
            $shopId = (int) ($this->request->getPost('shop_id') ?? 0);
        }

        $data = [
            'shop_id' => $shopId,
            'service_name' => trim((string) $this->request->getPost('service_name')),
            'description' => trim((string) $this->request->getPost('description')),
            'price' => (float) $this->request->getPost('price'),
            // duration is optional now; omit if not provided
            'is_active' => 1,
        ];
        $durationVal = $this->request->getPost('duration');
        if ($durationVal !== null && $durationVal !== '') {
            $data['duration'] = (int) $durationVal;
        }

        if (!$this->serviceModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', 'Failed to add service.');
        }

        return redirect()->to('services')->with('success', 'Service added successfully.');
    }

    public function employees()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role') ?? session()->get('user_role');
        if (!$userId || $role !== 'owner') {
            return redirect()->to('dashboard');
        }

        $shop = $this->shopModel->where('owner_id', $userId)->first();
        $employees = [];
        $availableBarbers = [];
        
        if ($shop) {
            $employees = $this->employeeModel->getEmployeesByShop($shop['shop_id']);
            $currentBarberIds = array_column($employees, 'user_id');
            
            // Get all available barbers (users with 'barber' role who are not already assigned to this shop)
            $availableQuery = $this->userModel
                ->where('role', 'barber')
                ->where('is_active', 1);
            
            if (!empty($currentBarberIds)) {
                $availableQuery->whereNotIn('user_id', $currentBarberIds);
            }
            
            $availableBarbers = $availableQuery->findAll();
        }

        return view('services/employees', [
            'title' => 'Manage Barbers',
            'shop' => $shop,
            'employees' => $employees,
            'available_barbers' => $availableBarbers,
            'user_role' => $role,
        ]);
    }

    public function edit(int $serviceId)
    {
        $userId = session()->get('user_id');
        $role = session()->get('role') ?? session()->get('user_role');
        if (!$userId || ($role !== 'owner' && $role !== 'admin')) {
            return redirect()->to('login');
        }

        $service = $this->serviceModel->find($serviceId);
        if (!$service) {
            return redirect()->to('services')->with('error', 'Service not found.');
        }

        if ($role === 'owner') {
            $shop = $this->shopModel->where('owner_id', $userId)->first();
            if (!is_array($shop) || (int) $shop['shop_id'] !== (int) $service['shop_id']) {
                return redirect()->to('services')->with('error', 'Unauthorized.');
            }
        }

        return view('services/edit', [
            'title' => 'Edit Service',
            'service' => $service,
            'user_role' => $role,
        ]);
    }

    public function update(int $serviceId)
    {
        $userId = session()->get('user_id');
        $role = session()->get('role') ?? session()->get('user_role');
        if (!$userId || ($role !== 'owner' && $role !== 'admin')) {
            return redirect()->to('login');
        }

        $service = $this->serviceModel->find($serviceId);
        if (!$service) {
            return redirect()->to('services')->with('error', 'Service not found.');
        }

        if ($role === 'owner') {
            $shop = $this->shopModel->where('owner_id', $userId)->first();
            if (!is_array($shop) || (int) $shop['shop_id'] !== (int) $service['shop_id']) {
                return redirect()->to('services')->with('error', 'Unauthorized.');
            }
        }

        $data = [
            'service_name' => trim((string) $this->request->getPost('service_name')),
            'description' => trim((string) $this->request->getPost('description')),
            'price' => (float) $this->request->getPost('price'),
        ];
        $durationVal = $this->request->getPost('duration');
        if ($durationVal !== null && $durationVal !== '') {
            $data['duration'] = (int) $durationVal;
        }

        if (!$this->serviceModel->update($serviceId, $data)) {
            return redirect()->back()->withInput()->with('error', 'Failed to update service.');
        }

        return redirect()->to('services')->with('success', 'Service updated successfully.');
    }

    public function delete(int $serviceId)
    {
        $userId = session()->get('user_id');
        $role = session()->get('role') ?? session()->get('user_role');
        if (!$userId || ($role !== 'owner' && $role !== 'admin')) {
            return redirect()->to('login');
        }

        $service = $this->serviceModel->find($serviceId);
        if (!$service) {
            return redirect()->to('services')->with('error', 'Service not found.');
        }

        if ($role === 'owner') {
            $shop = $this->shopModel->where('owner_id', $userId)->first();
            if (!is_array($shop) || (int) $shop['shop_id'] !== (int) $service['shop_id']) {
                return redirect()->to('services')->with('error', 'Unauthorized.');
            }
        }

        $this->serviceModel->delete($serviceId);
        return redirect()->to('services')->with('success', 'Service deleted.');
    }
    public function assignBarber()
    {
        $userId = session()->get('user_id');
        $role = session()->get('role') ?? session()->get('user_role');
        if (!$userId || $role !== 'owner') {
            return redirect()->to('dashboard');
        }

        $shop = $this->shopModel->where('owner_id', $userId)->first();
        if (!$shop) {
            return redirect()->back()->with('error', 'No shop found.');
        }

        $barberId = (int) $this->request->getPost('barber_id');
        if (!$barberId) {
            return redirect()->back()->with('error', 'Please select a barber.');
        }

        // Check if barber exists and has barber role
        $barber = $this->userModel->find($barberId);
        if (!$barber || $barber['role'] !== 'barber') {
            return redirect()->back()->with('error', 'Invalid barber selected.');
        }

        // Check if already assigned to this shop
        $existing = $this->employeeModel
            ->where('user_id', $barberId)
            ->where('shop_id', $shop['shop_id'])
            ->first();
        
        if ($existing) {
            return redirect()->back()->with('error', 'Barber is already assigned to your shop.');
        }

        // Assign barber to shop
        $this->employeeModel->insert([
            'user_id' => $barberId,
            'shop_id' => $shop['shop_id'],
            'position' => 'Barber',
            'hire_date' => date('Y-m-d'),
            'is_active' => 1,
        ]);

        return redirect()->to('services/employees')->with('success', 'Barber assigned to your shop successfully.');
    }
}
