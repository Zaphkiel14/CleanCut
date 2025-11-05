<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function login()
    {
        // If user is already logged in, redirect to dashboard
        if (session()->get('user_id')) {
            return redirect()->to('dashboard');
        }

        return view('auth/login', ['title' => 'Login - CleanCut']);
    }

    public function authenticate()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember');

        // Validate input
        if (empty($email) || empty($password)) {
            session()->setFlashdata('error', 'Please enter both email and password.');
            return redirect()->back()->withInput();
        }

        // Find user by email
        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            session()->setFlashdata('error', 'Invalid email or password.');
            return redirect()->back()->withInput();
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            session()->setFlashdata('error', 'Invalid email or password.');
            return redirect()->back()->withInput();
        }

        // Check if user is active
        if (!$user['is_active']) {
            session()->setFlashdata('error', 'Your account has been deactivated. Please contact administrator.');
            return redirect()->back()->withInput();
        }

        // Load shop information if user is an owner
        $shop_name = null;
        if ($user['role'] === 'owner') {
            $db = \Config\Database::connect();
            $shopBuilder = $db->table('shops');
            $shop = $shopBuilder->where('owner_id', $user['user_id'])->get()->getRowArray();
            if ($shop) {
                $shop_name = $shop['shop_name'];
            }
        }

        // Set session data
        session()->set([
            'user_id' => $user['user_id'],
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'user_email' => $user['email'],
            'role' => $user['role'], // standardized key
            'user_role' => $user['role'], // backward compatibility for legacy checks
            'shop_name' => $shop_name, // shop name for owners
            'is_logged_in' => true
        ]);

        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            // In a real application, you'd store this token in the database
            setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
        }

        // Redirect based on role
        $redirectUrl = 'dashboard';
        
        // Log the login
        $this->logLogin($user['user_id'], true);

        session()->setFlashdata('success', 'Welcome back, ' . $user['first_name'] . '!');
        return redirect()->to($redirectUrl);
    }

    public function logout()
    {
        // Log the logout
        if (session()->get('user_id')) {
            $this->logLogin(session()->get('user_id'), false);
        }

        // Clear session
        session()->destroy();

        // Clear remember me cookie
        setcookie('remember_token', '', time() - 3600, '/');

        session()->setFlashdata('success', 'You have been successfully logged out.');
        return redirect()->to('login');
    }

    private function logLogin($userId, $isLogin)
    {
        try {
            $db = \Config\Database::connect();
            
            // Get user info for logging
            $user = $this->userModel->find($userId);
            if (!$user) {
                return; // Skip logging if user not found
            }
            
            $action = $isLogin ? 'login' : 'logout';
            
            $sql = "INSERT INTO login_logs (user_id, username, role, action, login_time) 
                    VALUES (?, ?, ?, ?, NOW())";
            
            $db->query($sql, [
                $userId,
                $user['first_name'] . ' ' . $user['last_name'],
                $user['role'],
                $action
            ]);
        } catch (\Exception $e) {
            // Log error silently - don't break the login process
            log_message('error', 'Failed to log login: ' . $e->getMessage());
        }
    }
}
