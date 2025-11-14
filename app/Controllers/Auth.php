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

            // Redirect to dashboard after successful login
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

        $userModel = new \App\Models\UserModel();
        $role = strtolower(session('role') ?? '');
        $section = $this->request->getGet('section') ?? 'overview';
        
        // Fetch role-specific data from database
        $data = [
            'role' => $role,
            'section' => $section,
            'user' => [
                'id' => session('userID'),
                'name' => session('name'),
                'email' => session('email'),
                'role' => session('role'),
            ],
        ];

        // Load models
        $courseModel = new \App\Models\CourseModel();
        $materialModel = new \App\Models\MaterialModel();
        $enrollmentModel = new \App\Models\EnrollmentModel();
        $assignmentModel = new \App\Models\AssignmentModel();
        $assignmentSubmissionModel = new \App\Models\AssignmentSubmissionModel();
        $academicYearModel = new \App\Models\AcademicYearModel();
        $semesterModel = new \App\Models\SemesterModel();
        $yearLevelModel = new \App\Models\YearLevelModel();
        
        // Get current academic year and semester
        $currentAcademicYear = $academicYearModel->where('is_active', 1)->first();
        $currentSemester = null;
        if ($currentAcademicYear) {
            $currentSemester = $semesterModel->where('academic_year_id', $currentAcademicYear['id'])
                                            ->where('is_active', 1)
                                            ->first();
        }
        $data['currentAcademicYear'] = $currentAcademicYear;
        $data['currentSemester'] = $currentSemester;
        
        // Get student's year level if they are a student
        if ($role === 'student') {
            $userData = $userModel->find(session('userID'));
            if ($userData && isset($userData['year_level_id']) && $userData['year_level_id']) {
                $data['studentYearLevel'] = $yearLevelModel->find($userData['year_level_id']);
            }
        }

        // Role-specific data fetching based on section
        if ($role === 'admin') {
            $data['totalUsers'] = $userModel->countAllResults();
            $data['recentUsers'] = $userModel->orderBy('created_at', 'DESC')->limit(5)->findAll();
            
            // Section-specific data
            if ($section === 'users') {
                $data['allUsers'] = $userModel->findAll();
            } elseif ($section === 'courses') {
                $data['courses'] = $courseModel->findAll();
                // Get material counts for each course
                foreach ($data['courses'] as &$course) {
                    $course['material_count'] = $materialModel->where('course_id', $course['id'])->countAllResults();
                }
            } elseif ($section === 'academic-years') {
                $academicYearModel = new \App\Models\AcademicYearModel();
                $data['academicYears'] = $academicYearModel->orderBy('year_start', 'DESC')->findAll();
            } elseif ($section === 'semesters') {
                $semesterModel = new \App\Models\SemesterModel();
                $academicYearModel = new \App\Models\AcademicYearModel();
                $data['semesters'] = $semesterModel->orderBy('created_at', 'DESC')->findAll();
                $data['academicYears'] = $academicYearModel->findAll();
                // Join academic year info for each semester
                foreach ($data['semesters'] as &$semester) {
                    $semester['academic_year'] = $academicYearModel->find($semester['academic_year_id']);
                }
            } elseif ($section === 'year-levels') {
                $yearLevelModel = new \App\Models\YearLevelModel();
                $data['yearLevels'] = $yearLevelModel->orderBy('level', 'ASC')->findAll();
            } elseif ($section === 'assign-year-level') {
                $userModel = new \App\Models\UserModel();
                $yearLevelModel = new \App\Models\YearLevelModel();
                $data['students'] = $userModel->where('role', 'student')->findAll();
                $data['yearLevels'] = $yearLevelModel->orderBy('level', 'ASC')->findAll();
                // Get year level info for each student
                foreach ($data['students'] as &$student) {
                    if ($student['year_level_id']) {
                        $student['year_level'] = $yearLevelModel->find($student['year_level_id']);
                    }
                }
            } elseif ($section === 'upload') {
                // Get course ID from query if provided
                $courseId = $this->request->getGet('course_id');
                if ($courseId) {
                    $course = $courseModel->find($courseId);
                    if ($course) {
                        $data['course'] = $course;
                        $data['materials'] = $materialModel->getMaterialsByCourse($courseId);
                    }
                } else {
                    // Show all courses for selection
                    $data['courses'] = $courseModel->findAll();
                }
            } elseif ($section === 'materials') {
                // Get course ID from query
                $courseId = $this->request->getGet('course_id');
                if ($courseId) {
                    $course = $courseModel->find($courseId);
                    if ($course) {
                        $data['course'] = $course;
                        $data['materials'] = $materialModel->getMaterialsByCourse($courseId);
                    }
                }
            }
        } elseif ($role === 'teacher') {
            $data['totalStudents'] = $userModel->where('role', 'student')->countAllResults();
            
            // Section-specific data
            if ($section === 'my-courses') {
                $courses = $courseModel->findAll();
                $coursesWithMaterials = [];
                foreach ($courses as $course) {
                    $materialCount = $materialModel->where('course_id', $course['id'])->countAllResults();
                    $course['material_count'] = $materialCount;
                    $coursesWithMaterials[] = $course;
                }
                $data['courses'] = $coursesWithMaterials;
            } elseif ($section === 'upload') {
                // Get course ID from query if provided
                $courseId = $this->request->getGet('course_id');
                if ($courseId) {
                    $course = $courseModel->find($courseId);
                    if ($course) {
                        $data['course'] = $course;
                        $data['materials'] = $materialModel->getMaterialsByCourse($courseId);
                    }
                } else {
                    // Show all courses for selection
                    $data['courses'] = $courseModel->findAll();
                }
            } elseif ($section === 'enroll-students') {
                // Get all courses and students for enrollment management
                $data['courses'] = $courseModel->findAll();
                $data['students'] = $userModel->where('role', 'student')->findAll();
                
                // Get course ID from query if provided to show enrolled students
                $courseId = $this->request->getGet('course_id');
                if ($courseId) {
                    $data['selectedCourse'] = $courseModel->find($courseId);
                    // Get enrolled students for this course
                    $db = \Config\Database::connect();
                    $enrolledQuery = $db->query("
                        SELECT e.*, u.id as user_id, u.name, u.email
                        FROM enrollments e
                        JOIN users u ON u.id = e.user_id
                        WHERE e.course_id = ? AND u.role = 'student'
                        ORDER BY e.created_at DESC
                    ", [$courseId]);
                    $data['enrolledStudents'] = $enrolledQuery->getResultArray();
                }
            } elseif ($section === 'create-assignment') {
                // Get all courses for assignment creation
                $data['courses'] = $courseModel->findAll();
            } elseif ($section === 'assignments') {
                // Get course ID from query if provided
                $courseId = $this->request->getGet('course_id');
                if ($courseId) {
                    $data['course'] = $courseModel->find($courseId);
                    $data['assignments'] = $assignmentModel->getAssignmentsByCourse($courseId);
                } else {
                    // Show all courses for selection
                    $data['courses'] = $courseModel->findAll();
                }
            } elseif ($section === 'view-assignment') {
                // Get assignment ID from query
                $assignmentId = $this->request->getGet('assignment_id');
                if ($assignmentId) {
                    $data['assignment'] = $assignmentModel->getAssignmentWithDetails($assignmentId);
                    if ($data['assignment']) {
                        $data['submissions'] = $assignmentSubmissionModel->getSubmissionsByAssignment($assignmentId);
                    }
                }
            }
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
                    SELECT e.*, c.title, c.description, c.id as course_id
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
            
            // Format enrollments for display
            $data['enrollments'] = [];
            foreach ($enrolledCourses as $course) {
                $data['enrollments'][] = [
                    'course' => $course['title'],
                    'instructor' => 'Instructor',
                    'status' => 'Active',
                    'course_id' => $course['course_id']
                ];
            }
            
            // Section-specific data
            if ($section === 'enrollments') {
                // Already loaded above
            } elseif ($section === 'assignments') {
                // Get assignments for enrolled courses
                $data['assignments'] = $assignmentModel->getAssignmentsForEnrolledCourses(session('userID'));
                // Check submission status for each assignment
                foreach ($data['assignments'] as &$assignment) {
                    $assignment['has_submitted'] = $assignmentSubmissionModel->hasUserSubmitted(session('userID'), $assignment['id']);
                    if ($assignment['has_submitted']) {
                        $assignment['submission'] = $assignmentSubmissionModel->getSubmissionByUserAndAssignment(session('userID'), $assignment['id']);
                    }
                }
            } elseif ($section === 'view-assignment') {
                // Get assignment ID from query
                $assignmentId = $this->request->getGet('assignment_id');
                if ($assignmentId) {
                    $assignment = $assignmentModel->getAssignmentWithDetails($assignmentId);
                    if ($assignment) {
                        // Check if student is enrolled
                        if ($enrollmentModel->isAlreadyEnrolled(session('userID'), $assignment['course_id'])) {
                            $data['assignment'] = $assignment;
                            $data['submission'] = $assignmentSubmissionModel->getSubmissionByUserAndAssignment(session('userID'), $assignmentId);
                            $data['has_submitted'] = $data['submission'] !== null;
                        } else {
                            session()->setFlashdata('error', 'You are not enrolled in this course.');
                        }
                    }
                }
            } elseif ($section === 'materials') {
                // Get course ID from query
                $courseId = $this->request->getGet('course_id');
                if ($courseId) {
                    // Check if user is enrolled
                    if ($enrollmentModel->isAlreadyEnrolled(session('userID'), $courseId)) {
                        $course = $courseModel->find($courseId);
                        if ($course) {
                            $data['course'] = $course;
                            $data['materials'] = $materialModel->getMaterialsByCourse($courseId);
                        }
                    } else {
                        session()->setFlashdata('error', 'You are not enrolled in this course.');
                    }
                }
            }
        }

        return view('auth/dashboard', $data);
    }
}
