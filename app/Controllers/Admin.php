<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Admin extends BaseController
{
    public function dashboard()
    {
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'Please log in first.');
            return redirect()->to('/login');
        }

        $role = strtolower(session('role') ?? '');
        if ($role !== 'admin') {
            session()->setFlashdata('error', 'Access denied: Admins only.');
            return redirect()->to('/login');
        }

        return view('admin/dashboard');
    }
}


