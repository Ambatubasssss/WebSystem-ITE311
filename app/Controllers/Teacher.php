<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Teacher extends BaseController
{
    public function dashboard()
    {
        // Redirect to unified dashboard
        return redirect()->to('/dashboard');
    }

    public function courses()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'teacher') {
            session()->setFlashdata('error', 'Access denied. Teacher role required.');
            return redirect()->to('/dashboard');
        }

        // Redirect to unified dashboard with my-courses section
        return redirect()->to('/dashboard?section=my-courses');
    }

    public function grades()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'teacher') {
            session()->setFlashdata('error', 'Access denied. Teacher role required.');
            return redirect()->to('/dashboard');
        }

        // Redirect to unified dashboard (grades can be added later)
        return redirect()->to('/dashboard');
    }
}
