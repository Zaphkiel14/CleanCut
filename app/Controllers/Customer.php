<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Customer extends Controller
{
    public function dashboard()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'customer') {
            return redirect()->to('/login');
        }
        return view('customer_dashboard');
    }
}
