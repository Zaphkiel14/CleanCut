<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ShopModel;
use App\Models\EmployeeModel;

class UserController extends BaseController
{
    protected $userModel;
    protected $shopModel;
    protected $employeeModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->shopModel = new ShopModel();
        $this->employeeModel = new EmployeeModel();
    }

    // Show user management page
    public function index()
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('role') ?? session()->get('user_role');

        if (!in_array($userRole, ['admin', 'owner'])) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        // Get users based on role
        if ($userRole === 'admin') {
            // Admin can see all users
            $users = $this->userModel->findAll();
            $shops = $this->shopModel->findAll();
        } else {
            // Owner can see users in their shop
            $shop = $this->shopModel->where('owner_id', $userId)->first();
            if (!$shop) {
                return redirect()->to('/dashboard')->with('error', 'Shop not found.');
            }
            
            // Get employees in the shop and their user details
            $employees = $this->employeeModel->getEmployeesByShop($shop['shop_id']);
            $userIds = array_column($employees, 'user_id');
            $userIds[] = $userId; // Include the owner
            
            $users = $this->userModel->whereIn('user_id', $userIds)->findAll();
            $shops = [$shop];
        }

        $data = [
            'title' => 'User Management',
            'user_role' => $userRole,
            'users' => $users,
            'shops' => $shops,
        ];

        return view('users/index', $data);
    }

    // Show create user form
    public function create()
    {
        $userRole = session()->get('role') ?? session()->get('user_role');

        if (!in_array($userRole, ['admin', 'owner'])) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $data = [
            'title' => 'Add New User',
            'user_role' => $userRole,
        ];

        return view('users/create', $data);
    }

    // Store new user
    public function store()
    {
        $userRole = session()->get('role') ?? session()->get('user_role');

        if (!in_array($userRole, ['admin', 'owner'])) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name'  => 'required|min_length[2]|max_length[50]',
            'email'      => 'required|valid_email|is_unique[users.email]',
            'phone'      => 'required|min_length[10]|max_length[15]',
            'password'   => 'required|min_length[6]',
            'role'       => 'required|in_list[customer,barber,admin,owner]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
            'phone'      => $this->request->getPost('phone'),
            'password'   => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'       => $this->request->getPost('role'),
            'is_active'  => 1,
        ];

        $result = $this->userModel->insert($data);

        if ($result) {
            return redirect()->to('/users')->with('success', 'User created successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to create user.');
        }
    }

    // Show edit user form
    public function edit(int $id)
    {
        $userRole = session()->get('role') ?? session()->get('user_role');
        $currentUserId = session()->get('user_id');

        if (!in_array($userRole, ['admin', 'owner'])) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'User not found.');
        }

        // Owner can only edit users in their shop
        if ($userRole === 'owner') {
            $shop = $this->shopModel->where('owner_id', $currentUserId)->first();
            if (!$shop) {
                return redirect()->to('/dashboard')->with('error', 'Shop not found.');
            }
            
            $employee = $this->employeeModel->where('user_id', $id)->where('shop_id', $shop['shop_id'])->first();
            if (!$employee && $id != $currentUserId) {
                return redirect()->to('/users')->with('error', 'Unauthorized.');
            }
        }

        $data = [
            'title' => 'Edit User',
            'user_role' => $userRole,
            'user' => $user,
        ];

        return view('users/edit', $data);
    }

    // Update user
    public function update(int $id)
    {
        $userRole = session()->get('role') ?? session()->get('user_role');
        $currentUserId = session()->get('user_id');

        if (!in_array($userRole, ['admin', 'owner'])) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'User not found.');
        }

        $rules = [
            'first_name' => 'required|min_length[2]|max_length[50]',
            'last_name'  => 'required|min_length[2]|max_length[50]',
            'email'      => "required|valid_email|is_unique[users.email,user_id,{$id}]",
            'phone'      => 'required|min_length[10]|max_length[15]',
            'role'       => 'required|in_list[customer,barber,admin,owner]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name'  => $this->request->getPost('last_name'),
            'email'      => $this->request->getPost('email'),
            'phone'      => $this->request->getPost('phone'),
            'role'       => $this->request->getPost('role'),
        ];

        // Update password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $result = $this->userModel->update($id, $data);

        if ($result) {
            return redirect()->to('/users')->with('success', 'User updated successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update user.');
        }
    }

    // Toggle user status
    public function toggleStatus(int $id)
    {
        $userRole = session()->get('role') ?? session()->get('user_role');

        if (!in_array($userRole, ['admin', 'owner'])) {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return $this->response->setJSON(['error' => 'User not found']);
        }

        $newStatus = $user['is_active'] ? 0 : 1;
        $result = $this->userModel->update($id, ['is_active' => $newStatus]);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'status' => $newStatus,
                'message' => 'User status updated successfully'
            ]);
        } else {
            return $this->response->setJSON(['error' => 'Failed to update user status']);
        }
    }

    // Delete user
    public function delete(int $id)
    {
        $userRole = session()->get('role') ?? session()->get('user_role');

        if ($userRole !== 'admin') {
            return redirect()->to('/users')->with('error', 'Only admins can delete users.');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to('/users')->with('error', 'User not found.');
        }

        // Don't allow deleting the current user
        if ($id == session()->get('user_id')) {
            return redirect()->to('/users')->with('error', 'Cannot delete your own account.');
        }

        $result = $this->userModel->delete($id);

        if ($result) {
            return redirect()->to('/users')->with('success', 'User deleted successfully.');
        } else {
            return redirect()->to('/users')->with('error', 'Failed to delete user.');
        }
    }
}
