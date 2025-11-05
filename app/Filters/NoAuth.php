<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class NoAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('isLoggedIn')) {
            // Optional: redirect based on role
            $role = session()->get('role');
            if ($role === 'admin') {
                return redirect()->to('/admin');
            } elseif ($role === 'barber') {
                return redirect()->to('/barber');
            } elseif ($role === 'client') {
                return redirect()->to('/client');
            }

                return redirect()->to('/home'); // fallback
            
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // not used
    }
}
