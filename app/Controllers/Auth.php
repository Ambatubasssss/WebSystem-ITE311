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

            // Enhanced password validation
            if (strlen($password) < 8) {
                session()->setFlashdata('error', 'Password must be at least 8 characters.');
                return view('auth/register');
            }

            if (!preg_match('/[A-Z]/', $password)) {
                session()->setFlashdata('error', 'Password must contain at least one uppercase letter.');
                return view('auth/register');
            }

            if (!preg_match('/[a-z]/', $password)) {
                session()->setFlashdata('error', 'Password must contain at least one lowercase letter.');
                return view('auth/register');
            }

            if (!preg_match('/[0-9]/', $password)) {
                session()->setFlashdata('error', 'Password must contain at least one number.');
                return view('auth/register');
            }

            if (!preg_match('/[^A-Za-z0-9]/', $password)) {
                session()->setFlashdata('error', 'Password must contain at least one special character.');
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
            // Rate limiting: Check for too many login attempts
            $ip = $this->request->getIPAddress();
            $attemptsKey = 'login_attempts_' . $ip;
            $attempts = session($attemptsKey) ?? 0;
            
            if ($attempts >= 5) {
                session()->setFlashdata('error', 'Too many login attempts. Please try again later.');
                return view('auth/login');
            }
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
                $attempts++;
                session()->set($attemptsKey, $attempts);
                session()->setFlashdata('error', 'Invalid credentials. Attempts remaining: ' . (5 - $attempts));
                return view('auth/login');
            }

            // Check password verification
            if (!password_verify($password, $user['password'])) {
                $attempts++;
                session()->set($attemptsKey, $attempts);
                session()->setFlashdata('error', 'Invalid credentials. Attempts remaining: ' . (5 - $attempts));
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
            
            // Clear login attempts on successful login
            session()->remove($attemptsKey);

            // Role-based redirection after successful login
            session()->setFlashdata('success', 'Welcome back, ' . $user['name'] . '!');
            
            // Redirect users based on their role
            $userRole = strtolower($user['role']);
            
            switch ($userRole) {
                case 'student':
                    return redirect()->to('/announcements');
                case 'teacher':
                    return redirect()->to('/teacher/dashboard');
                case 'admin':
                    return redirect()->to('/admin/dashboard');
                default:
                    return redirect()->to('/dashboard');
            }
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

        $userModel = new \App\Models\UserModel();
        $role = strtolower(session('role') ?? '');
        
        // Fetch role-specific data from database
        $data = [
            'role' => $role,
            'user' => [
                'id' => session('userID'),
                'name' => session('name'),
                'email' => session('email'),
                'role' => session('role'),
            ],
        ];

        // Role-specific data fetching
        if ($role === 'admin') {
            $data['totalUsers'] = $userModel->countAllResults();
            $data['totalCourses'] = 0; // Placeholder - would need courses table
            $data['recentUsers'] = $userModel->orderBy('created_at', 'DESC')->limit(5)->findAll();
        } elseif ($role === 'teacher') {
            $data['myCourses'] = ['Math 101', 'Science 202']; // Placeholder
            $data['pendingAssignments'] = 5; // Placeholder
            $data['totalStudents'] = $userModel->where('role', 'student')->countAllResults();
        } elseif ($role === 'student') {
            // Get enrolled courses from database (with error handling)
            $enrolledCourses = [];
            $availableCourses = [];
            
            try {
                $db = \Config\Database::connect();
                
                // Get all courses from database
                $allCoursesQuery = $db->query("SELECT id, title, description FROM courses ORDER BY id");
                $allCourses = $allCoursesQuery->getResultArray();
                
                // Get enrolled courses
                $enrollmentsQuery = $db->query("
                    SELECT e.*, c.title, c.description 
                    FROM enrollments e 
                    JOIN courses c ON c.id = e.course_id 
                    WHERE e.user_id = ? 
                    ORDER BY e.created_at DESC
                ", [session('userID')]);
                $enrolledCourses = $enrollmentsQuery->getResultArray();
                
                // Get enrolled course IDs
                $enrolledCourseIds = array_column($enrolledCourses, 'course_id');
                
                // Filter available courses (not enrolled)
                foreach ($allCourses as $course) {
                    if (!in_array($course['id'], $enrolledCourseIds)) {
                        $availableCourses[] = $course;
                    }
                }
            } catch (\Exception $e) {
                // If database query fails, use empty arrays
                log_message('error', 'Dashboard enrollment query failed: ' . $e->getMessage());
                $enrolledCourses = [];
                $availableCourses = [];
            }
            
            $data['enrolledCourses'] = $enrolledCourses;
            $data['availableCourses'] = $availableCourses;
            $data['upcomingDeadlines'] = ['Assignment 1 (Oct 1)', 'Quiz 2 (Oct 5)']; // Placeholder
            $data['completedAssignments'] = 3; // Placeholder
            
            // Format enrollments for display
            $data['enrollments'] = [];
            foreach ($enrolledCourses as $course) {
                $data['enrollments'][] = [
                    'course' => $course['title'],
                    'instructor' => 'Instructor', // You can add instructor info if needed
                    'status' => 'Active',
                    'course_id' => $course['course_id']
                ];
            }
            $data['assignments'] = [
                ['title' => 'Assignment 1', 'course' => 'History 101', 'due_date' => '2024-10-01', 'status' => 'Pending'],
                ['title' => 'Quiz 2', 'course' => 'Art 303', 'due_date' => '2024-10-05', 'status' => 'Completed']
            ];
        }

        return view('auth/dashboard', $data);
    }
}
