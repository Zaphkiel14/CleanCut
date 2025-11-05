<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Role implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $userRole = $session->get('role'); // Assume 'role' is stored on login

        if (!$userRole) {
            return redirect()->to('/login');
        }

        // Check if user's role is allowed
        if (!in_array($userRole, $arguments)) {
            return redirect()->to('/unauthorized'); // Or any custom error page
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Not used
    }
}
