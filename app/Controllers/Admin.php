<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Admin extends BaseController
{
    public function users()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard');
        }

        $userModel = new \App\Models\UserModel();
        $data = [
            'users' => $userModel->findAll(),
            'title' => 'User Management'
        ];

        return view('admin/users', $data);
    }

    public function settings()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'System Settings'
        ];

        return view('admin/settings', $data);
    }
}
