<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Student extends BaseController
{
    public function enrollments()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'student') {
            session()->setFlashdata('error', 'Access denied. Student role required.');
            return redirect()->to('/dashboard');
        }

        // Redirect to unified dashboard with enrollment data
        session()->setFlashdata('show_enrollments', true);
        return redirect()->to('/dashboard');
    }

    public function assignments()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'student') {
            session()->setFlashdata('error', 'Access denied. Student role required.');
            return redirect()->to('/dashboard');
        }

        // Redirect to unified dashboard with assignment data
        session()->setFlashdata('show_assignments', true);
        return redirect()->to('/dashboard');
    }
}
