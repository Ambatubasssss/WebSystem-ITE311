<?php namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('home');
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('contact');
    }

    public function error403()
    {
        // Get user role from session
        $role = strtolower(session('role') ?? '');
        $dashboardUrl = '/ITE311-MALILAY/';

        // Redirect to appropriate dashboard based on role
        if ($role === 'admin') {
            $dashboardUrl = '/ITE311-MALILAY/admin/dashboard';
        } elseif ($role === 'teacher') {
            $dashboardUrl = '/ITE311-MALILAY/teacher/dashboard';
        } elseif ($role === 'student') {
            $dashboardUrl = '/ITE311-MALILAY/dashboard';
        } elseif (session()->get('logged_in')) {
            $dashboardUrl = '/ITE311-MALILAY/dashboard';
        } else {
            $dashboardUrl = '/ITE311-MALILAY/login';
        }

        // Auto-redirect after 2 seconds
        $data = [
            'dashboardUrl' => $dashboardUrl,
            'role' => $role
        ];

        return view('errors/html/error_403', $data);
    }
}
