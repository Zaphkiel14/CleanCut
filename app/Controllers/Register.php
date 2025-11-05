<?php

namespace App\Controllers;

use App\Models\UserModel;

class Register extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // If user is already logged in, redirect to dashboard
        if (session()->get('user_id')) {
            return redirect()->to('dashboard');
        }

        return view('auth/register', ['title' => 'Register - CleanCut']);
    }

    public function store()
    {
        // Validate input
        $rules = [
            'first_name' => 'required|min_length[2]|max_length[100]',
            'last_name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'role' => 'required|in_list[customer,barber,owner,admin]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Prepare user data (manually hash password since callbacks are disabled)
        $userData = [
            'first_name' => $this->request->getPost('first_name'),
            'last_name' => $this->request->getPost('last_name'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => $this->request->getPost('role'),
            'is_active' => true
        ];

        // Add optional phone field if provided (map contact_info to phone)
        if ($this->request->getPost('contact_info')) {
            $userData['phone'] = $this->request->getPost('contact_info');
        }

        // Save user
        try {
            // Debug: Log the data being saved
            log_message('info', 'Registration data: ' . json_encode($userData));

            $this->userModel->save($userData);

            session()->setFlashdata('success', 'Account created successfully! Please login.');
            return redirect()->to('login');
        } catch (\Exception $e) {
            // Log the actual error for debugging
            log_message('error', 'Registration failed: ' . $e->getMessage());

            // Show the actual error message to help debug
            session()->setFlashdata('error', 'Failed to create account: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }
}
