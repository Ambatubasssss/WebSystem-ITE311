<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Student extends BaseController
{
    public function dashboard()
    {
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'Please log in first.');
            return redirect()->to('/login');
        }

        $role = strtolower(session('role') ?? '');
        if ($role !== 'student') {
            session()->setFlashdata('error', 'Access denied: Students only.');
            return redirect()->to('/login');
        }

        return view('student/dashboard');
    }
}


