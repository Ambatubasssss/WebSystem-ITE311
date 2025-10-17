<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Admin extends BaseController
{
    public function dashboard()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/announcements');
        }

        $data = [
            'title' => 'Admin Dashboard'
        ];

        return view('admin_dashboard', $data);
    }

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

    public function getUsers()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        $userModel = new \App\Models\UserModel();
        $users = $userModel->findAll();

        return $this->response->setJSON([
            'success' => true,
            'users' => $users
        ]);
    }

    public function updateRole($userId)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        // Prevent editing admin role
        if (strtolower($user['role']) === 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Admin role cannot be edited.'
            ]);
        }

        $newRole = $this->request->getPost('role');

        // Validate role
        if (!in_array($newRole, ['teacher', 'student'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid role selected.'
            ]);
        }

        // Update user role
        $updateData = [
            'role' => $newRole,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($userModel->update($userId, $updateData)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User role updated successfully.',
                'newRole' => $newRole
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update user role.'
            ]);
        }
    }
}
