<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
    public function register()
    {
        // Check if form was submitted (POST request)
        if ($this->request->getMethod() === 'POST') {
            // Get form data
            $name = $this->request->getPost('name');
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $password_confirm = $this->request->getPost('password_confirm');

            // Simple validation
            if (empty($name) || empty($email) || empty($password) || empty($password_confirm)) {
                session()->setFlashdata('error', 'All fields are required.');
                return view('auth/register');
            }

            if ($password !== $password_confirm) {
                session()->setFlashdata('error', 'Passwords do not match.');
                return view('auth/register');
            }

            if (strlen($password) < 6) {
                session()->setFlashdata('error', 'Password must be at least 6 characters.');
                return view('auth/register');
            }

            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Save user data to database
            $userModel = new \App\Models\UserModel();
            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
                'role' => 'student',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];



            try {
                $result = $userModel->insert($userData);
                
                if ($result) {
                    // Set flash message and redirect to login
                    session()->setFlashdata('success', 'Registration successful! Please login.');
                    return redirect()->to('/login');
                } else {
                    // Debug: Show the error
                    $errors = $userModel->errors();
                    session()->setFlashdata('error', 'Registration failed. Errors: ' . json_encode($errors));
                }
            } catch (\Exception $e) {
                session()->setFlashdata('error', 'Registration failed: ' . $e->getMessage());
            }
        }

        // Load the registration view
        return view('auth/register');
    }

    public function login()
    {
        // Check if form was submitted (POST request)
        if ($this->request->getMethod() === 'POST') {
            // Get form data
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            // Simple validation
            if (empty($email) || empty($password)) {
                session()->setFlashdata('error', 'Email and password are required.');
                return view('auth/login');
            }

            // Check database for user
            $userModel = new \App\Models\UserModel();
            $user = $userModel->where('email', $email)->first();

            // Check if user was found
            if (!$user) {
                session()->setFlashdata('error', 'User not found with email: ' . $email);
                return view('auth/login');
            }

            // Check password verification
            if (!password_verify($password, $user['password'])) {
                session()->setFlashdata('error', 'Invalid password for user: ' . $email);
                return view('auth/login');
            }

            // Credentials are correct, create session
            $sessionData = [
                'userID' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'logged_in' => true
            ];
            
            session()->set($sessionData);

            // Unified dashboard redirection for all roles
            session()->setFlashdata('success', 'Welcome back, ' . $user['name'] . '!');
            return redirect()->to('/dashboard');
        }

        // For GET requests, just load the login view
        return view('auth/login');
    }

    public function logout()
    {
        // Destroy the current session
        session()->destroy();
        
        // Redirect to homepage
        return redirect()->to('/');
    }

    public function dashboard()
    {
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'You must be logged in to access the dashboard.');
            return redirect()->to('/login');
        }

        // Prepare role-specific data (placeholder data for now)
        $role = strtolower(session('role') ?? '');
        $data = [
            'role' => $role,
            'user' => [
                'id' => session('userID'),
                'name' => session('name'),
                'email' => session('email'),
            ],
            'stats' => [
                'totalUsers' => null,
                'totalCourses' => null,
            ],
        ];

        return view('auth/dashboard', $data);
    }
}
