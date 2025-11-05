<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Admin extends Controller
{
    public function dashboard()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/login');
        }
        return view('admin_dashboard');
    }
}
