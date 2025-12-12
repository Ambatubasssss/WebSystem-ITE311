<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Auth extends BaseController
{
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

            // Set validation rules
            $validation = \Config\Services::validation();
            $validation->setRules([
                'email' => [
                    'label' => 'Email',
                    'rules' => 'required|valid_email|max_length[255]',
                    'errors' => [
                        'required' => 'The {field} field is required.',
                        'valid_email' => 'Please provide a valid email address.',
                        'max_length' => 'The {field} cannot exceed {param} characters.'
                    ]
                ],
                'password' => [
                    'label' => 'Password',
                    'rules' => 'required|min_length[1]',
                    'errors' => [
                        'required' => 'The {field} field is required.',
                        'min_length' => 'The {field} is required.'
                    ]
                ]
            ]);

            // Run validation
            if (!$validation->withRequest($this->request)->run()) {
                // Validation failed
                $errors = $validation->getErrors();
                session()->setFlashdata('error', implode('<br>', $errors));
                return view('auth/login', ['validation' => $validation]);
            }

            // Get validated and sanitized form data
            $email = trim(strtolower($this->request->getPost('email')));
            $password = $this->request->getPost('password');

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

            // Check if user is active
            $isActive = isset($user['is_active']) ? (int)$user['is_active'] : 1;
            if (!$isActive) {
                session()->setFlashdata('error', 'Your account has been deactivated. Please contact an administrator.');
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
            
            // Regenerate session ID to prevent session fixation attacks
            session()->regenerate(true);
            
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
        
        // Authorization: Validate section access based on user role
        $adminSections = ['users', 'courses', 'academic-years', 'semesters', 'year-levels', 'assign-year-level', 'upload', 'materials', 'settings'];
        $teacherSections = ['my-courses', 'upload', 'enroll-students', 'create-assignment', 'assignments', 'view-assignment', 'materials', 'settings'];
        $studentSections = ['enrollments', 'assignments', 'view-assignment', 'materials', 'settings'];
        
        // Check if user is trying to access unauthorized section
        if ($section !== 'overview') {
            $authorized = false;
            if ($role === 'admin' && in_array($section, $adminSections)) {
                $authorized = true;
            } elseif ($role === 'teacher' && in_array($section, $teacherSections)) {
                $authorized = true;
            } elseif ($role === 'student' && in_array($section, $studentSections)) {
                $authorized = true;
            }
            
            if (!$authorized) {
                session()->setFlashdata('error', 'Access denied. You do not have permission to access this section.');
                $section = 'overview'; // Reset to overview
            }
        }
        
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

        // First, automatically update active semesters based on current date (Asia/Manila timezone)
        try {
            $activationService = new \App\Libraries\SemesterActivationService();
            $activationService->updateActiveSemesters();
        } catch (\Exception $e) {
            // Log error but don't break the dashboard
            log_message('error', 'Error updating active semesters: ' . $e->getMessage());
        }
        
        // Check and update course/enrollment completion status
        // This should only complete courses from semesters that are NOT active
        try {
            $completionService = new \App\Libraries\CompletionService();
            $completionService->checkAndUpdateCompletions();
            // Check and process completed academic years
            $completionService->checkAndProcessCompletedAcademicYears();
        } catch (\Exception $e) {
            // Log error but don't break the dashboard
            log_message('error', 'Error checking completions: ' . $e->getMessage());
        }

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
        $studentYearLevelId = null;
        if ($role === 'student') {
            $userData = $userModel->find(session('userID'));
            if ($userData && isset($userData['year_level_id']) && $userData['year_level_id']) {
                $studentYearLevelId = $userData['year_level_id'];
                $data['studentYearLevel'] = $yearLevelModel->find($studentYearLevelId);
            }
        }

        // Role-specific data fetching based on section
        if ($role === 'admin') {
            $data['totalUsers'] = $userModel->countAllResults();
            $data['recentUsers'] = $userModel->orderBy('created_at', 'DESC')->limit(5)->findAll();
            
            // Calculate total courses count for admin (all courses including completed, active, and unavailable)
            // Admins can see all courses, so count everything except soft-deleted
            $data['totalCourses'] = $courseModel->where('deleted_at', null)->countAllResults();
            
            // Section-specific data
            if ($section === 'users') {
                $data['allUsers'] = $userModel->findAll();
            } elseif ($section === 'courses') {
                // Load academic data for course creation form and filters
                $academicYearModel = new \App\Models\AcademicYearModel();
                $semesterModel = new \App\Models\SemesterModel();
                $yearLevelModel = new \App\Models\YearLevelModel();
                $data['academicYears'] = $academicYearModel->orderBy('year_start', 'DESC')->findAll();
                $data['allSemesters'] = $semesterModel->orderBy('created_at', 'DESC')->findAll();
                $data['yearLevels'] = $yearLevelModel->orderBy('level', 'ASC')->findAll();
                
                // Get filter parameters from query string
                $filterAcademicYearId = $this->request->getGet('academic_year_id');
                $filterSemester = $this->request->getGet('semester'); // 1st or 2nd
                $filterTerm = $this->request->getGet('term'); // 1st or 2nd (half semester each)
                
                // Build course query with filters
                $courseQuery = $courseModel;
                
                // Filter by academic year if provided
                if (!empty($filterAcademicYearId)) {
                    $courseQuery->where('academic_year_id', $filterAcademicYearId);
                    $data['selectedAcademicYearId'] = $filterAcademicYearId;
                    
                    // Filter semesters by academic year for the semester dropdown
                    $data['semesters'] = $semesterModel->where('academic_year_id', $filterAcademicYearId)->orderBy('semester', 'ASC')->orderBy('term', 'ASC')->findAll();
                } else {
                    $data['semesters'] = $data['allSemesters'];
                }
                
                // Filter by semester and term if provided (requires academic year)
                if (!empty($filterSemester) && !empty($filterTerm) && !empty($filterAcademicYearId)) {
                    // Find the semester_id that matches the academic year, semester, and term
                    $semesterFilter = $semesterModel->where('academic_year_id', $filterAcademicYearId)
                                                    ->where('semester', $filterSemester)
                                                    ->where('term', $filterTerm)
                                                    ->first();
                    
                    if ($semesterFilter) {
                        $courseQuery->where('semester_id', $semesterFilter['id']);
                        $data['selectedSemesterId'] = $semesterFilter['id'];
                        $data['selectedSemester'] = $filterSemester;
                        $data['selectedTerm'] = $filterTerm;
                    }
                } elseif (!empty($filterSemester) && !empty($filterTerm)) {
                    // Semester and term selected but no academic year - clear the filter
                    $data['selectedSemester'] = null;
                    $data['selectedTerm'] = null;
                }
                
                // Get filtered courses (exclude soft deleted and completed - like GALORPOT's flow)
                // Only show active courses (status is null, 'active', or not 'completed')
                $courseQuery->groupStart()
                           ->where('status !=', 'completed')
                           ->orWhere('status', null)
                           ->groupEnd();
                $allActiveCourses = $courseQuery->orderBy('title', 'ASC')->findAll();
                
                // Get the CURRENT ACTIVE semester (where is_active = 1)
                $currentActiveSemester = null;
                try {
                    $currentActiveSemester = $semesterModel->where('is_active', 1)->first();
                } catch (\Exception $e) {
                    log_message('error', 'Error getting current active semester: ' . $e->getMessage());
                }
                
                // Separate courses into: current active semester/term vs future vs past
                // Based on semester dates (Asia/Manila timezone)
                date_default_timezone_set('Asia/Manila');
                $today = date('Y-m-d');
                
                $currentSemesterCourses = [];
                $futureCourses = []; // Courses from future terms/semesters (start_date > today)
                $pastCourses = []; // Courses from past semesters (end_date < today) - should be in completed
                
                if ($currentActiveSemester) {
                    foreach ($allActiveCourses as $course) {
                        if (!empty($course['semester_id'])) {
                            try {
                                $courseSemester = $semesterModel->find($course['semester_id']);
                                if ($courseSemester) {
                                    $semesterStartDate = $courseSemester['start_date'] ?? null;
                                    $semesterEndDate = $courseSemester['end_date'] ?? null;
                                    
                                    // Check if course is from the current active semester and term
                                    if ($courseSemester['id'] == $currentActiveSemester['id']) {
                                        // Course is from current active semester and term - add to active courses
                                        $currentSemesterCourses[] = $course;
                                    } elseif ($semesterStartDate && $semesterEndDate) {
                                        // Check if semester is in the future (start_date > today)
                                        if ($semesterStartDate > $today) {
                                            // Future semester - add to future courses (prerequisite section)
                                            $futureCourses[] = $course;
                                        } elseif ($semesterEndDate < $today) {
                                            // Past semester - should be in completed, not in active courses
                                            $pastCourses[] = $course;
                                        } else {
                                            // Semester is ongoing but not active (edge case) - treat as current
                                            $currentSemesterCourses[] = $course;
                                        }
                                    } else {
                                        // Semester has no dates - compare by ID (backward compatibility)
                                        $futureCourses[] = $course;
                                    }
                                } else {
                                    // If semester not found, include in current courses (backward compatibility)
                                    $currentSemesterCourses[] = $course;
                                }
                            } catch (\Exception $e) {
                                log_message('error', 'Error processing course semester: ' . $e->getMessage());
                                $currentSemesterCourses[] = $course;
                            }
                        } else {
                            // If course has no semester, include in current courses
                            $currentSemesterCourses[] = $course;
                        }
                    }
                } else {
                    // If no active semester found, check dates to separate current vs future vs past
                    foreach ($allActiveCourses as $course) {
                        if (!empty($course['semester_id'])) {
                            try {
                                $courseSemester = $semesterModel->find($course['semester_id']);
                                if ($courseSemester) {
                                    $semesterStartDate = $courseSemester['start_date'] ?? null;
                                    $semesterEndDate = $courseSemester['end_date'] ?? null;
                                    
                                    if ($semesterStartDate && $semesterEndDate) {
                                        if ($semesterStartDate > $today) {
                                            // Future semester
                                            $futureCourses[] = $course;
                                        } elseif ($semesterEndDate < $today) {
                                            // Past semester
                                            $pastCourses[] = $course;
                                        } else {
                                            // Current semester (dates include today)
                                            $currentSemesterCourses[] = $course;
                                        }
                                    } else {
                                        // No dates - include in current (backward compatibility)
                                        $currentSemesterCourses[] = $course;
                                    }
                                } else {
                                    $currentSemesterCourses[] = $course;
                                }
                            } catch (\Exception $e) {
                                log_message('error', 'Error processing course semester: ' . $e->getMessage());
                                $currentSemesterCourses[] = $course;
                            }
                        } else {
                            $currentSemesterCourses[] = $course;
                        }
                    }
                }
                
                // Reset timezone
                date_default_timezone_set(date_default_timezone_get());
                
                // Get unavailable courses (Term 2 courses waiting for Term 1 completion)
                try {
                    // Pass user ID to check enrollment status for prerequisites
                    $userId = session('userID');
                    $data['unavailable_courses'] = $courseModel->getUnavailableCourses($filterAcademicYearId, null, $userId);
                    
                    // Add future courses (from different semesters/terms) to unavailable courses
                    // These are courses from future semesters (start_date > today)
                    if (!empty($futureCourses)) {
                        foreach ($futureCourses as $futureCourse) {
                            // Check if not already in unavailable courses
                            $alreadyExists = false;
                            if (!empty($data['unavailable_courses'])) {
                                foreach ($data['unavailable_courses'] as $unavCourse) {
                                    if (isset($unavCourse['id']) && isset($futureCourse['id']) && $unavCourse['id'] == $futureCourse['id']) {
                                        $alreadyExists = true;
                                        break;
                                    }
                                }
                            }
                            
                            if (!$alreadyExists) {
                                // Add semester info to the course
                                if (!empty($futureCourse['semester_id'])) {
                                    try {
                                        $futureSemester = $semesterModel->find($futureCourse['semester_id']);
                                        if ($futureSemester) {
                                            $semesterStartDate = $futureSemester['start_date'] ?? null;
                                            $futureCourse['semester_info'] = $futureSemester;
                                            
                                            // Set appropriate reason based on date
                                            if ($semesterStartDate && $semesterStartDate > $today) {
                                                $futureCourse['unavailable_reason'] = 'This course is from a future semester or term (starts on ' . date('M d, Y', strtotime($semesterStartDate)) . ')';
                                            } else {
                                                $futureCourse['unavailable_reason'] = 'This course is from a different semester or term';
                                            }
                                            
                                            $data['unavailable_courses'][] = $futureCourse;
                                        }
                                    } catch (\Exception $e) {
                                        log_message('error', 'Error processing future course semester: ' . $e->getMessage());
                                    }
                                }
                            }
                        }
                    }
                    
                    // Get unavailable course IDs to exclude from active courses
                    $unavailableCourseIds = !empty($data['unavailable_courses']) ? array_column($data['unavailable_courses'], 'id') : [];
                    
                    // Filter out unavailable courses from current semester courses
                    $data['courses'] = array_filter($currentSemesterCourses, function($course) use ($unavailableCourseIds) {
                        return !in_array($course['id'] ?? 0, $unavailableCourseIds);
                    });
                    $data['courses'] = array_values($data['courses']); // Re-index array
                } catch (\Exception $e) {
                    // If there's an error getting unavailable courses, just show current semester courses
                    log_message('error', 'Error getting unavailable courses: ' . $e->getMessage());
                    $data['courses'] = $currentSemesterCourses;
                    $data['unavailable_courses'] = !empty($futureCourses) ? $futureCourses : [];
                }
                
                // Ensure unavailable_courses is always set
                if (!isset($data['unavailable_courses'])) {
                    $data['unavailable_courses'] = [];
                }
                
                // Get completed courses separately
                // Only show courses that are truly completed (from past, inactive semesters)
                try {
                    // Set timezone to Asia/Manila for date comparison
                    date_default_timezone_set('Asia/Manila');
                    $today = date('Y-m-d');
                    
                    // Get completed courses that belong to semesters that:
                    // 1. Course status is 'completed'
                    // 2. Semester is either inactive OR has end_date that has passed
                    // 3. Also include courses with no semester (semester_id IS NULL) if they're completed
                    // This ensures completed courses show even if semester dates aren't set properly
                    $db = \Config\Database::connect();
                    $completedCoursesQuery = $db->query("
                        SELECT c.* 
                        FROM courses c
                        LEFT JOIN semesters s ON c.semester_id = s.id
                        WHERE c.status = 'completed'
                        AND c.deleted_at IS NULL
                        AND (
                            c.semester_id IS NULL
                            OR s.id IS NULL
                            OR s.is_active = 0 
                            OR (s.end_date IS NOT NULL AND s.end_date < ?)
                        )
                        ORDER BY c.completed_at DESC
                    ", [$today]);
                    $data['completed_courses'] = $completedCoursesQuery->getResultArray();
                    
                    // Reset timezone
                    date_default_timezone_set(date_default_timezone_get());
                } catch (\Exception $e) {
                    log_message('error', 'Error getting completed courses: ' . $e->getMessage());
                    $data['completed_courses'] = [];
                    // Reset timezone in case of error
                    date_default_timezone_set(date_default_timezone_get());
                }
                
                // Ensure completed_courses is always set
                if (!isset($data['completed_courses'])) {
                    $data['completed_courses'] = [];
                }
                
                // Get deleted courses separately (like GALORPOT's flow)
                $data['deleted_courses'] = $courseModel->withDeleted()
                                                       ->onlyDeleted()
                                                       ->orderBy('deleted_at', 'DESC')
                                                       ->findAll();
                
                // Get material counts and academic info for each course
                foreach ($data['courses'] as &$course) {
                    $course['material_count'] = $materialModel->where('course_id', $course['id'])->countAllResults();
                    
                    // Get academic year info
                    if ($course['academic_year_id']) {
                        $course['academic_year'] = $academicYearModel->find($course['academic_year_id']);
                    }
                    
                    // Get semester info
                    if ($course['semester_id']) {
                        $course['semester'] = $semesterModel->find($course['semester_id']);
                    }
                    
                    // Get year level info
                    if ($course['year_level_id']) {
                        $course['year_level'] = $yearLevelModel->find($course['year_level_id']);
                    }
                }
                
                // Get material counts and academic info for completed courses
                foreach ($data['completed_courses'] as &$course) {
                    $course['material_count'] = $materialModel->where('course_id', $course['id'])->countAllResults();
                    
                    // Get academic year info
                    if ($course['academic_year_id']) {
                        $course['academic_year'] = $academicYearModel->find($course['academic_year_id']);
                    }
                    
                    // Get semester info
                    if ($course['semester_id']) {
                        $course['semester'] = $semesterModel->find($course['semester_id']);
                    }
                    
                    // Get year level info
                    if ($course['year_level_id']) {
                        $course['year_level'] = $yearLevelModel->find($course['year_level_id']);
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
            } elseif ($section === 'materials') {
                // Get course ID from query
                $courseId = $this->request->getGet('course_id');
                if ($courseId) {
                    // Admin can view all courses - no need to check teacher assignment
                    $course = $courseModel->find($courseId);
                    if ($course) {
                        $data['course'] = $course;
                        $data['materials'] = $materialModel->getMaterialsByCourse($courseId);
                    } else {
                        session()->setFlashdata('error', 'Course not found.');
                        return redirect()->to('/dashboard');
                    }
                }
            }
        } elseif ($role === 'teacher') {
            $data['totalStudents'] = $userModel->where('role', 'student')->countAllResults();
            
            // Get teacher's user ID
            $teacherId = session('userID');
            $courseTeacherModel = new \App\Models\CourseTeacherModel();
            
            // Calculate teacher's course count for overview (exclude completed courses)
            $teacherCourses = $courseTeacherModel->getCoursesByTeacher($teacherId);
            $activeCoursesCount = 0;
            foreach ($teacherCourses as $teacherCourse) {
                $course = $courseModel->find($teacherCourse['course_id']);
                if ($course && $course['status'] !== 'completed') {
                    $activeCoursesCount++;
                }
            }
            $data['myCoursesCount'] = $activeCoursesCount;
            
            // Section-specific data
            if ($section === 'my-courses') {
                // Get only courses assigned to this teacher (exclude completed courses)
                $teacherCourses = $courseTeacherModel->getCoursesByTeacher($teacherId);
                $coursesWithMaterials = [];
                
                foreach ($teacherCourses as $teacherCourse) {
                    $course = $courseModel->find($teacherCourse['course_id']);
                    if ($course) {
                        // Filter out completed courses - they should not appear in "My Courses"
                        if ($course['status'] === 'completed') {
                            continue; // Skip completed courses
                        }
                        
                        $materialCount = $materialModel->where('course_id', $course['id'])->countAllResults();
                        $course['material_count'] = $materialCount;
                        $course['is_primary'] = $teacherCourse['is_primary'];
                        $coursesWithMaterials[] = $course;
                    }
                }
                $data['courses'] = $coursesWithMaterials;
            } elseif ($section === 'upload') {
                // Get course ID from query if provided
                $courseId = $this->request->getGet('course_id');
                if ($courseId) {
                    // Verify the teacher is assigned to this course
                    if ($courseTeacherModel->isTeacherAssigned($courseId, $teacherId)) {
                        $course = $courseModel->find($courseId);
                        if ($course) {
                            // Check if course is completed - redirect if so
                            if ($course['status'] === 'completed') {
                                session()->setFlashdata('error', 'This course has been completed and is no longer available.');
                                return redirect()->to('/dashboard?section=upload');
                            }
                            $data['course'] = $course;
                            $data['materials'] = $materialModel->getMaterialsByCourse($courseId);
                        }
                    } else {
                        session()->setFlashdata('error', 'You are not assigned to this course.');
                        return redirect()->to('/dashboard?section=upload');
                    }
                } else {
                    // Show only courses assigned to this teacher for selection (exclude completed)
                    $teacherCourses = $courseTeacherModel->getCoursesByTeacher($teacherId);
                    $assignedCourses = [];
                    foreach ($teacherCourses as $teacherCourse) {
                        $course = $courseModel->find($teacherCourse['course_id']);
                        if ($course && $course['status'] !== 'completed') {
                            $assignedCourses[] = $course;
                        }
                    }
                    $data['courses'] = $assignedCourses;
                }
            } elseif ($section === 'enroll-students') {
                // Get only courses assigned to this teacher (exclude completed)
                $teacherCourses = $courseTeacherModel->getCoursesByTeacher($teacherId);
                $assignedCourses = [];
                foreach ($teacherCourses as $teacherCourse) {
                    $course = $courseModel->find($teacherCourse['course_id']);
                    if ($course && $course['status'] !== 'completed') {
                        $assignedCourses[] = $course;
                    }
                }
                $data['courses'] = $assignedCourses;
                $data['students'] = []; // Initialize empty array - will be populated when course is selected
                
                // Get course ID from query if provided to show enrolled students
                $courseId = $this->request->getGet('course_id');
                if ($courseId) {
                    // Verify the teacher is assigned to this course
                    if ($courseTeacherModel->isTeacherAssigned($courseId, $teacherId)) {
                        $selectedCourse = $courseModel->find($courseId);
                        // Check if course is completed - redirect if so
                        if ($selectedCourse && $selectedCourse['status'] === 'completed') {
                            session()->setFlashdata('error', 'This course has been completed and is no longer available for enrollment.');
                            return redirect()->to('/dashboard?section=enroll-students');
                        }
                        $data['selectedCourse'] = $selectedCourse;
                        
                        // Filter students by course's year level
                        $studentQuery = $userModel->where('role', 'student');
                        if ($selectedCourse && !empty($selectedCourse['year_level_id'])) {
                            // Only show students matching the course's year level
                            $studentQuery->where('year_level_id', $selectedCourse['year_level_id']);
                        }
                        $data['students'] = $studentQuery->findAll();
                        
                        // Get approved enrolled students for this course
                        $db = \Config\Database::connect();
                        
                        // Check if status column exists (for backward compatibility)
                        $fields = $db->getFieldNames('enrollments');
                        $hasStatusColumn = in_array('status', $fields);
                        
                        if ($hasStatusColumn) {
                            $enrolledQuery = $db->query("
                                SELECT e.id as enrollment_id, e.course_id, e.user_id, e.enrollment_date, e.status, e.approved_at, e.approved_by, e.rejected_at, e.rejected_by, e.rejection_reason, e.created_at, e.updated_at, u.id as user_id, u.name, u.email
                                FROM enrollments e
                                JOIN users u ON u.id = e.user_id
                                WHERE e.course_id = ? AND u.role = 'student' AND e.status = 'approved'
                                ORDER BY e.created_at DESC
                            ", [$courseId]);
                        } else {
                            // If status column doesn't exist, get all enrollments (backward compatibility)
                            $enrolledQuery = $db->query("
                                SELECT e.id as enrollment_id, e.course_id, e.user_id, e.enrollment_date, e.created_at, e.updated_at, u.id as user_id, u.name, u.email
                                FROM enrollments e
                                JOIN users u ON u.id = e.user_id
                                WHERE e.course_id = ? AND u.role = 'student'
                                ORDER BY e.created_at DESC
                            ", [$courseId]);
                        }
                        $data['enrolledStudents'] = $enrolledQuery->getResultArray();
                        
                        // Get pending enrollments for this course
                        try {
                            $data['pendingEnrollments'] = $enrollmentModel->getPendingEnrollments($courseId);
                        } catch (\Exception $e) {
                            log_message('error', 'Error getting pending enrollments: ' . $e->getMessage());
                            $data['pendingEnrollments'] = [];
                        }
                    } else {
                        session()->setFlashdata('error', 'You are not assigned to this course.');
                        return redirect()->to('/dashboard?section=enroll-students');
                    }
                }
            } elseif ($section === 'create-assignment') {
                // Get only courses assigned to this teacher for assignment creation (exclude completed)
                $teacherCourses = $courseTeacherModel->getCoursesByTeacher($teacherId);
                $assignedCourses = [];
                foreach ($teacherCourses as $teacherCourse) {
                    $course = $courseModel->find($teacherCourse['course_id']);
                    if ($course && $course['status'] !== 'completed') {
                        $assignedCourses[] = $course;
                    }
                }
                $data['courses'] = $assignedCourses;
            } elseif ($section === 'assignments') {
                // Get course ID from query if provided
                $courseId = $this->request->getGet('course_id');
                if ($courseId) {
                    // Verify the teacher is assigned to this course
                    if ($courseTeacherModel->isTeacherAssigned($courseId, $teacherId)) {
                        $data['course'] = $courseModel->find($courseId);
                        $data['assignments'] = $assignmentModel->getAssignmentsByCourse($courseId);
                    } else {
                        session()->setFlashdata('error', 'You are not assigned to this course.');
                        return redirect()->to('/dashboard?section=assignments');
                    }
                } else {
                    // Show only courses assigned to this teacher for selection (exclude completed)
                    $teacherCourses = $courseTeacherModel->getCoursesByTeacher($teacherId);
                    $assignedCourses = [];
                    foreach ($teacherCourses as $teacherCourse) {
                        $course = $courseModel->find($teacherCourse['course_id']);
                        if ($course && $course['status'] !== 'completed') {
                            $assignedCourses[] = $course;
                        }
                    }
                    $data['courses'] = $assignedCourses;
                }
            } elseif ($section === 'view-assignment') {
                // Get assignment ID from query
                $assignmentId = $this->request->getGet('assignment_id');
                if ($assignmentId) {
                    $assignment = $assignmentModel->getAssignmentWithDetails($assignmentId);
                    if ($assignment) {
                        // Verify the teacher is assigned to the course
                        $courseId = $assignment['course_id'] ?? null;
                        if ($courseId && $courseTeacherModel->isTeacherAssigned($courseId, $teacherId)) {
                            $data['assignment'] = $assignment;
                            $data['submissions'] = $assignmentSubmissionModel->getSubmissionsByAssignment($assignmentId);
                        } else {
                            session()->setFlashdata('error', 'You are not assigned to this course.');
                            return redirect()->to('/dashboard?section=assignments');
                        }
                    }
                }
            }
        } elseif ($role === 'student') {
            // Get enrolled courses from database (with error handling)
            $enrolledCourses = [];
            $availableCourses = [];
            
            try {
                $db = \Config\Database::connect();
                $userId = session('userID');
                
                // Use the student's year level already fetched above
                
                // Get all enrollments (for status display)
                $allEnrollmentsQuery = $db->query("
                    SELECT e.*, c.title, c.description, c.id as course_id, c.year_level_id, e.status, e.approved_at, e.rejected_at, e.rejection_reason
                    FROM enrollments e 
                    JOIN courses c ON c.id = e.course_id 
                    WHERE e.user_id = ? 
                    ORDER BY e.created_at DESC
                ", [$userId]);
                $allEnrollments = $allEnrollmentsQuery->getResultArray();
                
                // Get only approved enrollments for enrolled courses list (exclude completed courses)
                $enrolledCourses = array_filter($allEnrollments, function($enrollment) {
                    // Only show approved enrollments that are not completed
                    return $enrollment['status'] === 'approved';
                });
                
                // Further filter: Remove courses that are marked as completed
                $enrolledCourses = array_filter($enrolledCourses, function($enrollment) use ($courseModel) {
                    $course = $courseModel->find($enrollment['course_id']);
                    // Exclude if course is completed
                    return $course && $course['status'] !== 'completed';
                });
                
                $enrolledCourses = array_values($enrolledCourses);
                
                // Get enrolled course IDs (all statuses) to exclude from available courses
                $enrolledCourseIds = array_column($allEnrollments, 'course_id');
                
                // Store all enrollments for status display
                $data['allEnrollments'] = $allEnrollments;
                
                // Build query for available courses - filter by year level if student has one
                // Exclude completed courses from available courses
                if ($studentYearLevelId) {
                    // Only show courses matching the student's year level (exclude completed)
                    $allCoursesQuery = $db->query("
                        SELECT id, title, description, year_level_id 
                        FROM courses 
                        WHERE deleted_at IS NULL 
                        AND year_level_id = ? 
                        AND (status IS NULL OR status != 'completed')
                        ORDER BY title ASC
                    ", [$studentYearLevelId]);
                } else {
                    // If student has no year level, show all courses (for backwards compatibility, exclude completed)
                    $allCoursesQuery = $db->query("
                        SELECT id, title, description, year_level_id 
                        FROM courses 
                        WHERE deleted_at IS NULL 
                        AND (status IS NULL OR status != 'completed')
                        ORDER BY title ASC
                    ");
                }
                $allCourses = $allCoursesQuery->getResultArray();
                
                // Filter available courses (not enrolled and matching year level)
                // Get student's year level
                $studentYearLevelId = null;
                if (isset($userData['year_level_id']) && $userData['year_level_id']) {
                    $studentYearLevelId = $userData['year_level_id'];
                }
                
                // Get unavailable courses (courses with prerequisites not completed)
                $unavailableCourseIds = [];
                try {
                    $unavailableCourses = $courseModel->getUnavailableCourses(null, null, $userId);
                    $unavailableCourseIds = !empty($unavailableCourses) ? array_column($unavailableCourses, 'id') : [];
                } catch (\Exception $e) {
                    log_message('error', 'Error getting unavailable courses for student: ' . $e->getMessage());
                }
                
                foreach ($allCourses as $course) {
                    // Skip if already enrolled
                    if (in_array($course['id'], $enrolledCourseIds)) {
                        continue;
                    }
                    
                    // Skip if course is unavailable (has prerequisites not completed)
                    if (in_array($course['id'], $unavailableCourseIds)) {
                        continue;
                    }
                    
                    // Double-check: Skip if course is completed (extra safety)
                    $fullCourse = $courseModel->find($course['id']);
                    if ($fullCourse && $fullCourse['status'] === 'completed') {
                        continue; // Skip completed courses
                    }
                    
                    // Filter by year level - only show courses matching student's year level
                    if ($studentYearLevelId) {
                        if (empty($course['year_level_id']) || $course['year_level_id'] != $studentYearLevelId) {
                            continue; // Skip courses that don't match the student's year level
                        }
                    }
                    
                    $availableCourses[] = $course;
                }
                
                // Store unavailable courses for display in prerequisite section
                $data['unavailable_courses'] = $unavailableCourses ?? [];
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
                        // Check if student is approved and enrolled
                        if ($enrollmentModel->isApprovedEnrolled(session('userID'), $assignment['course_id'])) {
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
                    // Check if user is approved and enrolled
                    if ($enrollmentModel->isApprovedEnrolled(session('userID'), $courseId)) {
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

        // Load user data for settings section
        if ($section === 'settings') {
            $currentUser = $userModel->find(session('userID'));
            $data['currentUser'] = $currentUser;
            $data['settingsTab'] = $this->request->getGet('tab') ?? 'profile';
        }

        return view('auth/dashboard', $data);
    }

    public function updateProfile()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in to update your profile.'
            ]);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => [
                'label' => 'Name',
                'rules' => 'required|min_length[3]|max_length[100]|alpha_space',
                'errors' => [
                    'required' => 'The {field} field is required.',
                    'min_length' => 'The {field} must be at least {param} characters long.',
                    'max_length' => 'The {field} cannot exceed {param} characters.',
                    'alpha_space' => 'The {field} can only contain letters and spaces.'
                ]
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email|max_length[255]',
                'errors' => [
                    'required' => 'The {field} field is required.',
                    'valid_email' => 'Please provide a valid email address.',
                    'max_length' => 'The {field} cannot exceed {param} characters.'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            return $this->response->setJSON([
                'success' => false,
                'message' => implode('<br>', $errors),
                'errors' => $errors
            ]);
        }

        $userId = session('userID');
        $name = trim($this->request->getPost('name'));
        $email = trim(strtolower($this->request->getPost('email')));

        $userModel = new \App\Models\UserModel();
        $currentUser = $userModel->find($userId);

        if (!$currentUser) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        // Check if email is already taken by another user
        $existingUser = $userModel->where('email', $email)->where('id !=', $userId)->first();
        if ($existingUser) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'This email address is already registered to another user.'
            ]);
        }

        $updateData = [
            'name' => $name,
            'email' => $email,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            if ($userModel->update($userId, $updateData)) {
                // Update session data
                session()->set([
                    'name' => $name,
                    'email' => $email
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Profile updated successfully!'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update profile.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error updating profile: ' . $e->getMessage()
            ]);
        }
    }

    public function updatePassword()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in to update your password.'
            ]);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'current_password' => [
                'label' => 'Current Password',
                'rules' => 'required',
                'errors' => [
                    'required' => 'The {field} field is required.'
                ]
            ],
            'new_password' => [
                'label' => 'New Password',
                'rules' => 'required|min_length[8]|regex_match[/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/]',
                'errors' => [
                    'required' => 'The {field} field is required.',
                    'min_length' => 'The {field} must be at least {param} characters long.',
                    'regex_match' => 'The {field} must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
                ]
            ],
            'confirm_password' => [
                'label' => 'Confirm Password',
                'rules' => 'required|matches[new_password]',
                'errors' => [
                    'required' => 'Please confirm your new password.',
                    'matches' => 'The password confirmation does not match.'
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            return $this->response->setJSON([
                'success' => false,
                'message' => implode('<br>', $errors),
                'errors' => $errors
            ]);
        }

        $userId = session('userID');
        $currentPassword = $this->request->getPost('current_password');
        $newPassword = $this->request->getPost('new_password');

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Current password is incorrect.'
            ]);
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $updateData = [
            'password' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            if ($userModel->update($userId, $updateData)) {
                // Destroy session to force re-login with new password
                session()->destroy();
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Password updated successfully! You will be logged out. Please login again with your new password.',
                    'logout_required' => true
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update password.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error updating password: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Unified search method for all dashboard sections
     * Supports both client-side filtering and server-side AJAX search
     */
    public function search()
    {
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in to search.'
            ])->setStatusCode(401);
        }

        // Get search parameters
        $section = $this->request->getGet('section') ?? $this->request->getPost('section') ?? '';
        $searchTerm = trim($this->request->getGet('search_term') ?? $this->request->getPost('search_term') ?? '');
        $filterType = $this->request->getGet('filter_type') ?? $this->request->getPost('filter_type') ?? '';
        
        // Security: Sanitize search term
        $searchTerm = htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8');
        
        $userRole = strtolower(session('role') ?? '');
        $results = [];
        $message = '';

        try {
            switch ($section) {
                case 'courses':
                    if ($userRole !== 'admin') {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Access denied.'
                        ])->setStatusCode(403);
                    }
                    $courseModel = new \App\Models\CourseModel();
                    $query = $courseModel;
                    
                    if (!empty($searchTerm)) {
                        $query->groupStart()
                              ->like('title', $searchTerm)
                              ->orLike('control_number', $searchTerm)
                              ->orLike('description', $searchTerm)
                              ->groupEnd();
                    }
                    
                    if (!empty($filterType)) {
                        if ($filterType === 'active') {
                            $query->where('deleted_at', null);
                        } elseif ($filterType === 'deleted') {
                            $query->where('deleted_at !=', null);
                        }
                    }
                    
                    $results = $query->orderBy('title', 'ASC')->findAll();
                    $message = count($results) . ' course(s) found';
                    break;

                case 'users':
                    if ($userRole !== 'admin') {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Access denied.'
                        ])->setStatusCode(403);
                    }
                    $userModel = new \App\Models\UserModel();
                    $query = $userModel;
                    
                    if (!empty($searchTerm)) {
                        $query->groupStart()
                              ->like('name', $searchTerm)
                              ->orLike('email', $searchTerm)
                              ->groupEnd();
                    }
                    
                    if (!empty($filterType)) {
                        if ($filterType === 'active') {
                            $query->where('is_active', 1);
                        } elseif ($filterType === 'inactive') {
                            $query->where('is_active', 0);
                        } elseif (in_array($filterType, ['admin', 'teacher', 'student'])) {
                            $query->where('role', $filterType);
                        }
                    }
                    
                    $results = $query->orderBy('name', 'ASC')->findAll();
                    $message = count($results) . ' user(s) found';
                    break;

                case 'materials':
                    $materialModel = new \App\Models\MaterialModel();
                    $courseId = $this->request->getGet('course_id') ?? $this->request->getPost('course_id');
                    
                    $query = $materialModel;
                    
                    if ($courseId) {
                        $query->where('course_id', $courseId);
                    }
                    
                    if (!empty($searchTerm)) {
                        $query->groupStart()
                              ->like('file_name', $searchTerm)
                              ->orLike('file_type', $searchTerm)
                              ->groupEnd();
                    }
                    
                    $results = $query->orderBy('created_at', 'DESC')->findAll();
                    $message = count($results) . ' material(s) found';
                    break;

                case 'assignments':
                    $assignmentModel = new \App\Models\AssignmentModel();
                    $courseId = $this->request->getGet('course_id') ?? $this->request->getPost('course_id');
                    
                    $query = $assignmentModel;
                    
                    if ($courseId) {
                        $query->where('course_id', $courseId);
                    }
                    
                    if (!empty($searchTerm)) {
                        $query->groupStart()
                              ->like('title', $searchTerm)
                              ->orLike('description', $searchTerm)
                              ->groupEnd();
                    }
                    
                    if (!empty($filterType)) {
                        if ($filterType === 'active') {
                            $query->where('due_date >=', date('Y-m-d'));
                        } elseif ($filterType === 'past') {
                            $query->where('due_date <', date('Y-m-d'));
                        }
                    }
                    
                    $results = $query->orderBy('due_date', 'ASC')->findAll();
                    $message = count($results) . ' assignment(s) found';
                    break;

                case 'enrollments':
                case 'enroll-students':
                    $enrollmentModel = new \App\Models\EnrollmentModel();
                    
                    if ($userRole === 'student') {
                        $userId = session('userID');
                        $query = $enrollmentModel->where('user_id', $userId);
                    } else {
                        $courseId = $this->request->getGet('course_id') ?? $this->request->getPost('course_id');
                        if ($courseId) {
                            $query = $enrollmentModel->where('course_id', $courseId);
                        } else {
                            $query = $enrollmentModel;
                        }
                    }
                    
                    if (!empty($searchTerm)) {
                        // Join with users and courses for search
                        $userModel = new \App\Models\UserModel();
                        $courseModel = new \App\Models\CourseModel();
                        
                        // Get user IDs matching search
                        $matchingUsers = $userModel->select('id')
                                                   ->groupStart()
                                                   ->like('name', $searchTerm)
                                                   ->orLike('email', $searchTerm)
                                                   ->groupEnd()
                                                   ->findAll();
                        $userIds = array_column($matchingUsers, 'id');
                        
                        // Get course IDs matching search
                        $matchingCourses = $courseModel->select('id')
                                                      ->groupStart()
                                                      ->like('title', $searchTerm)
                                                      ->orLike('description', $searchTerm)
                                                      ->groupEnd()
                                                      ->findAll();
                        $courseIds = array_column($matchingCourses, 'id');
                        
                        if (!empty($userIds) || !empty($courseIds)) {
                            $query->groupStart();
                            if (!empty($userIds)) {
                                $query->whereIn('user_id', $userIds);
                            }
                            if (!empty($courseIds)) {
                                $query->orWhereIn('course_id', $courseIds);
                            }
                            $query->groupEnd();
                        } else {
                            // No matches, return empty
                            $results = [];
                            $message = 'No enrollments found';
                            break;
                        }
                    }
                    
                    if (!empty($filterType)) {
                        if ($filterType === 'approved') {
                            $query->where('status', 'approved');
                        } elseif ($filterType === 'pending') {
                            $query->where('status', 'pending');
                        } elseif ($filterType === 'rejected') {
                            $query->where('status', 'rejected');
                        }
                    }
                    
                    $results = $query->orderBy('created_at', 'DESC')->findAll();
                    $message = count($results) . ' enrollment(s) found';
                    break;

                default:
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Invalid search section.'
                    ])->setStatusCode(400);
            }

            return $this->response->setJSON([
                'success' => true,
                'results' => $results,
                'count' => count($results),
                'message' => $message,
                'search_term' => $searchTerm,
                'section' => $section
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Search error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred during search: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
