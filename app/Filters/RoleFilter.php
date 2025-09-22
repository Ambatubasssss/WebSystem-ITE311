<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('logged_in')) {
            $session->setFlashdata('error', 'Please log in first.');
            return redirect()->to('/login');
        }

        $requiredRole = isset($arguments[0]) ? strtolower($arguments[0]) : '';
        $currentRole  = strtolower($session->get('role') ?? '');

        if ($requiredRole && $currentRole !== $requiredRole) {
            $session->setFlashdata('error', 'Access denied.');
            return redirect()->to('/login');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}


