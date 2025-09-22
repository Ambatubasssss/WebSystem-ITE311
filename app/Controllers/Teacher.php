<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Teacher extends BaseController
{
    public function dashboard()
    {
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'Please log in first.');
            return redirect()->to('/login');
        }

        $role = strtolower(session('role') ?? '');
        if ($role !== 'teacher') {
            session()->setFlashdata('error', 'Access denied: Teachers only.');
            return redirect()->to('/login');
        }

        return view('teacher/dashboard');
    }
}


