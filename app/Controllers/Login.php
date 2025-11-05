<?php
namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Login extends Controller
{
    public function index()
    {
        return view('login');
    }

    public function auth()
    {
        $session = session();
        $request = service('request');
        $username = $request->getPost('username');
        $password = $request->getPost('password');

        // Example: Replace with your own user validation logic
        $userModel = new UserModel();
        $user = $userModel->where('username', $username)->first();

        if ($user && password_verify($password, $user['password'])) {
            $session->set('isLoggedIn', true);
            $session->set('username', $username);
            $session->set('role', $user['role']);

            // Log the login event
            $loginLogModel = new \App\Models\LoginLogModel();
            $loginLogModel->insert([
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'login_time' => date('Y-m-d H:i:s'),
            ]);

            if ($user['role'] === 'admin') {
                return redirect()->to('/admin/dashboard');
            } elseif ($user['role'] === 'customer') {
                return redirect()->to('/customer/dashboard');
            } else {
                return redirect()->to('/');
            }
        } else {
            return redirect()->back()->with('error', 'Invalid login credentials');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
