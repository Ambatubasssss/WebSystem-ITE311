<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CourseModel;
use App\Models\EnrollmentModel;

class Course extends BaseController
{
    protected $courseModel;
    protected $enrollmentModel;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->enrollmentModel = new EnrollmentModel();
    }

    /**
     * Handle course enrollment via AJAX
     */
    public function enroll()
    {
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in to enroll in courses.'
            ]);
        }

        $userRole = strtolower(session('role') ?? '');
        
        // Get course_id and optional student_id from POST request
        $courseId = $this->request->getPost('course_id');
        $studentId = $this->request->getPost('student_id'); // For teachers enrolling students
        
        // Determine the user ID to enroll
        if ($userRole === 'teacher' && !empty($studentId)) {
            // Teacher is enrolling a student
            $userId = $studentId;
        } elseif ($userRole === 'student') {
            // Student is enrolling themselves
            $userId = session('userID');
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid enrollment request.'
            ]);
        }

        // Validate course_id - prevent SQL injection by ensuring it's a valid integer
        if (empty($courseId) || !is_numeric($courseId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID provided.'
            ]);
        }

        // Cast to integer to prevent any string-based SQL injection attempts
        $courseId = (int) $courseId;
        
        // Additional validation: ensure course_id is positive
        if ($courseId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID provided.'
            ]);
        }

        // Get course from database - CodeIgniter's Query Builder automatically escapes parameters
        $course = $this->courseModel->find($courseId);
        
        // Check if course exists
        if (!$course) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        // Check if course has a teacher assigned (only for student self-enrollment)
        if ($userRole === 'student') {
            $courseTeacherModel = new \App\Models\CourseTeacherModel();
            $teachers = $courseTeacherModel->getTeachersByCourse($courseId);
            
            if (empty($teachers)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "There's no teacher assigned yet in this course. Please wait until a teacher is assigned before enrolling."
                ]);
            }
        }

        // Check for schedule conflicts (for both student self-enrollment and teacher enrollment)
        // This prevents students from being enrolled in conflicting courses
        $conflictCheck = $this->checkStudentScheduleConflict($courseId, $userId);
        if (!$conflictCheck['allowed']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $conflictCheck['message']
            ]);
        }
        
        // Check if user is already enrolled (any status) to prevent duplicates
        if ($this->enrollmentModel->isAlreadyEnrolled($userId, $courseId)) {
            // Check the status
            $existingEnrollment = $this->enrollmentModel->where('user_id', $userId)
                                                         ->where('course_id', $courseId)
                                                         ->first();
            if ($existingEnrollment) {
                $status = $existingEnrollment['status'] ?? 'pending';
                if ($status === 'pending') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'You already have a pending enrollment request for this course.'
                    ]);
                } elseif ($status === 'approved') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'You are already enrolled in this course.'
                    ]);
                } elseif ($status === 'rejected') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Your enrollment request for this course was rejected. Please contact the teacher.'
                    ]);
                }
            }
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You are already enrolled in this course.'
            ]);
        }

        // Insert enrollment record using model method
        try {
            $enrollmentData = [
                'user_id' => $userId,
                'course_id' => $courseId
            ];
            
            // Set status based on who is enrolling
            if ($userRole === 'teacher') {
                // Teacher enrolling student - automatically approved
                $enrollmentData['status'] = 'approved';
                $enrollmentData['approved_by'] = session('userID');
                $enrollmentData['approved_at'] = date('Y-m-d H:i:s');
            } else {
                // Student enrolling themselves - pending approval
                $enrollmentData['status'] = 'pending';
            }
            
            $result = $this->enrollmentModel->enrollUser($enrollmentData);
            
            if ($result) {
                // Create notification for successful enrollment
                $notificationModel = new \App\Models\NotificationModel();
                if ($userRole === 'teacher') {
                    // Get student name for notification
                    $userModel = new \App\Models\UserModel();
                    $student = $userModel->find($userId);
                    $studentName = $student ? $student['name'] : 'Student';
                    $notificationMessage = "You have been enrolled in " . $course['title'] . " by your teacher!";
                    $notificationModel->createNotification($userId, $notificationMessage, 'enrollment');
                    
                    // Notify teachers assigned to this course about the enrollment
                    $courseTeacherModel = new \App\Models\CourseTeacherModel();
                    $teachers = $courseTeacherModel->getTeachersByCourse($courseId);
                    foreach ($teachers as $teacher) {
                        $notificationModel->createNotification($teacher['teacher_id'], "Student {$studentName} has been enrolled in {$course['title']}.", 'enrollment');
                    }
                } else {
                    // Student enrolled - notify teachers for approval
                    $courseTeacherModel = new \App\Models\CourseTeacherModel();
                    $teachers = $courseTeacherModel->getTeachersByCourse($courseId);
                    $userModel = new \App\Models\UserModel();
                    $student = $userModel->find($userId);
                    $studentName = $student ? $student['name'] : 'Student';
                    
                    foreach ($teachers as $teacher) {
                        $notificationModel->createNotification($teacher['teacher_id'], "{$studentName} has requested enrollment in {$course['title']}. Please review and approve.", 'enrollment');
                    }
                    
                    $notificationMessage = "Your enrollment request for " . $course['title'] . " is pending teacher approval.";
                    $notificationModel->createNotification($userId, $notificationMessage, 'enrollment');
                }
                
                $successMessage = ($userRole === 'teacher') 
                    ? 'Successfully enrolled student in ' . $course['title'] . '!'
                    : 'Enrollment request submitted for ' . $course['title'] . '. Waiting for teacher approval.';
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $successMessage,
                    'status' => $enrollmentData['status'],
                    'course' => [
                        'id' => $course['id'],
                        'title' => $course['title'],
                        'description' => $course['description']
                    ],
                    'csrf_token' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to enroll in course. Please try again.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while enrolling: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get all available courses for enrollment
     * Filters by student's year level if they are a student
     */
    public function getAvailableCourses()
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
        
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in to view courses.'
            ])->setStatusCode(401);
        }

        $userId = session('userID');
        $userRole = strtolower(session('role') ?? '');
        
        // Get student's year level if they are a student
        $yearLevelId = null;
        if ($userRole === 'student') {
            $userModel = new \App\Models\UserModel();
            $userData = $userModel->find($userId);
            if ($userData && isset($userData['year_level_id']) && $userData['year_level_id']) {
                $yearLevelId = $userData['year_level_id'];
            }
        }
        
        // Get courses filtered by year level (if student)
        $courses = $this->courseModel->getCoursesNotEnrolledByUser($userId, $yearLevelId);

        return $this->response->setJSON([
            'success' => true,
            'courses' => $courses
        ]);
    }

    /**
     * Get user's enrolled courses
     */
    public function getUserEnrollments()
    {
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in to view enrollments.'
            ]);
        }

        $userId = session('userID');
        $enrollments = $this->enrollmentModel->getUserEnrollments($userId);

        return $this->response->setJSON([
            'success' => true,
            'enrollments' => $enrollments
        ]);
    }

    /**
     * Display course details - redirects to dashboard with materials section
     */
    public function view($courseId)
    {
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'You must be logged in to view courses.');
            return redirect()->to('/login');
        }

        // Validate course_id
        $courseId = (int) $courseId;
        if ($courseId <= 0) {
            session()->setFlashdata('error', 'Invalid course ID.');
            return redirect()->to('/dashboard');
        }

        $course = $this->courseModel->find($courseId);
        
        if (!$course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/dashboard');
        }

        // Get user role to determine appropriate redirect
        $userRole = strtolower(session('role') ?? '');
        
        // Check if user is enrolled (for students) or has access (for teachers/admins)
        if ($userRole === 'student') {
            // Check if student is enrolled
            if (!$this->enrollmentModel->isApprovedEnrolled(session('userID'), $courseId)) {
                session()->setFlashdata('error', 'You are not enrolled in this course.');
                return redirect()->to('/dashboard');
            }
        }
        
        // Redirect to unified dashboard with materials section to view course details
        return redirect()->to('/dashboard?section=materials&course_id=' . $courseId);
    }

    /**
     * Get all students (for teacher enrollment)
     */
    public function getStudents()
    {
        // Check if user is logged in and is a teacher
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in.'
            ]);
        }

        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Teacher/Admin privileges required.'
            ]);
        }

        $userModel = new \App\Models\UserModel();
        $students = $userModel->where('role', 'student')->findAll();

        return $this->response->setJSON([
            'success' => true,
            'students' => $students
        ]);
    }

    /**
     * Get students enrolled in a specific course
     */
    public function getCourseStudents($courseId)
    {
        // Check if user is logged in and is a teacher/admin
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in.'
            ]);
        }

        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Teacher/Admin privileges required.'
            ]);
        }

        $db = \Config\Database::connect();
        $query = $db->query("
            SELECT e.*, u.id as user_id, u.name, u.email, c.title as course_title
            FROM enrollments e
            JOIN users u ON u.id = e.user_id
            JOIN courses c ON c.id = e.course_id
            WHERE e.course_id = ? AND u.role = 'student'
            ORDER BY e.created_at DESC
        ", [$courseId]);

        $enrolledStudents = $query->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'students' => $enrolledStudents
        ]);
    }

    /**
     * Search courses by title or description
     * Supports both AJAX (JSON) and regular (view) requests
     */
    public function search()
    {
        // Get search term from GET or POST request
        $searchTerm = $this->request->getGet('search_term') ?? $this->request->getPost('search_term') ?? '';
        
        // Security: Sanitize search term to prevent SQL injection
        $searchTerm = trim($searchTerm);
        
        // Create a new model instance for this query to avoid modifying the shared instance
        $searchModel = new CourseModel();
        
        // Build query using CodeIgniter's Query Builder
        if (!empty($searchTerm)) {
            // Use LIKE queries for searching in title and description
            $searchModel->groupStart()
                        ->like('title', $searchTerm)
                        ->orLike('description', $searchTerm)
                        ->groupEnd();
        }
        
        // Get all matching courses
        $courses = $searchModel->orderBy('title', 'ASC')->findAll();
        
        // Check if request is AJAX
        if ($this->request->isAJAX()) {
            // Return JSON response for AJAX requests
            return $this->response->setJSON([
                'success' => true,
                'courses' => $courses,
                'count' => count($courses),
                'search_term' => $searchTerm
            ]);
        }
        
        // For regular requests, render view (if search_results view exists)
        $data = [
            'courses' => $courses,
            'searchTerm' => $searchTerm
        ];
        
        // Redirect to dashboard with search results
        return redirect()->to('/dashboard?section=enrollments&search=' . urlencode($searchTerm));
    }
    
    /**
     * Approve a pending enrollment (Teacher only)
     */
    public function approveEnrollment()
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
        
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in.'
            ])->setStatusCode(401);
        }
        
        $userRole = strtolower(session('role') ?? '');
        
        // Only teachers and admins can approve
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Teacher/Admin privileges required.'
            ])->setStatusCode(403);
        }
        
        $enrollmentId = $this->request->getPost('enrollment_id');
        
        if (empty($enrollmentId) || !is_numeric($enrollmentId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid enrollment ID provided.'
            ]);
        }
        
        $enrollmentId = (int) $enrollmentId;
        
        try {
            // Get enrollment details
            $enrollment = $this->enrollmentModel->find($enrollmentId);
            
            if (!$enrollment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Enrollment not found.'
                ]);
            }
            
            // If teacher, verify they are assigned to this course
            if ($userRole === 'teacher') {
                $courseTeacherModel = new \App\Models\CourseTeacherModel();
                $teacherId = session('userID');
                
                if (!$courseTeacherModel->isTeacherAssigned($enrollment['course_id'], $teacherId)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'You are not assigned to this course.'
                    ])->setStatusCode(403);
                }
            }
            
            // Check if already approved
            if ($enrollment['status'] === 'approved') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'This enrollment is already approved.'
                ]);
            }
            
            // Approve enrollment
            $approvedBy = session('userID');
            $result = $this->enrollmentModel->approveEnrollment($enrollmentId, $approvedBy);
            
            if ($result) {
                // Get course and student details for notification
                $courseModel = new \App\Models\CourseModel();
                $userModel = new \App\Models\UserModel();
                $course = $courseModel->find($enrollment['course_id']);
                $student = $userModel->find($enrollment['user_id']);
                
                // Notify student
                $notificationModel = new \App\Models\NotificationModel();
                $notificationModel->createNotification(
                    $enrollment['user_id'],
                    "Your enrollment request for {$course['title']} has been approved!",
                    'enrollment'
                );
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Enrollment approved successfully.',
                    'csrf_token' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to approve enrollment.'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error approving enrollment: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Reject a pending enrollment (Teacher only)
     */
    public function rejectEnrollment()
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
        
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in.'
            ])->setStatusCode(401);
        }
        
        $userRole = strtolower(session('role') ?? '');
        
        // Only teachers and admins can reject
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Teacher/Admin privileges required.'
            ])->setStatusCode(403);
        }
        
        $enrollmentId = $this->request->getPost('enrollment_id');
        $rejectionReason = $this->request->getPost('rejection_reason');
        
        if (empty($enrollmentId) || !is_numeric($enrollmentId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid enrollment ID provided.'
            ]);
        }
        
        $enrollmentId = (int) $enrollmentId;
        
        try {
            // Get enrollment details
            $enrollment = $this->enrollmentModel->find($enrollmentId);
            
            if (!$enrollment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Enrollment not found.'
                ]);
            }
            
            // If teacher, verify they are assigned to this course
            if ($userRole === 'teacher') {
                $courseTeacherModel = new \App\Models\CourseTeacherModel();
                $teacherId = session('userID');
                
                if (!$courseTeacherModel->isTeacherAssigned($enrollment['course_id'], $teacherId)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'You are not assigned to this course.'
                    ])->setStatusCode(403);
                }
            }
            
            // Check if already processed
            if ($enrollment['status'] !== 'pending') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'This enrollment has already been processed.'
                ]);
            }
            
            // Reject enrollment
            $rejectedBy = session('userID');
            $result = $this->enrollmentModel->rejectEnrollment($enrollmentId, $rejectedBy, $rejectionReason);
            
            if ($result) {
                // Get course details for notification
                $courseModel = new \App\Models\CourseModel();
                $course = $courseModel->find($enrollment['course_id']);
                
                // Notify student
                $notificationModel = new \App\Models\NotificationModel();
                $reasonText = $rejectionReason ? " Reason: {$rejectionReason}" : "";
                $notificationModel->createNotification(
                    $enrollment['user_id'],
                    "Your enrollment request for {$course['title']} has been rejected.{$reasonText}",
                    'enrollment'
                );
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Enrollment rejected successfully.',
                    'csrf_token' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to reject enrollment.'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error rejecting enrollment: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get pending enrollments for a course (Teacher only)
     */
    public function getPendingEnrollments($courseId = null)
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
        
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in.'
            ])->setStatusCode(401);
        }
        
        $userRole = strtolower(session('role') ?? '');
        
        // Only teachers and admins can view pending enrollments
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied.'
            ])->setStatusCode(403);
        }
        
        try {
            if ($courseId) {
                // Get pending enrollments for specific course
                if ($userRole === 'teacher') {
                    $courseTeacherModel = new \App\Models\CourseTeacherModel();
                    $teacherId = session('userID');
                    
                    if (!$courseTeacherModel->isTeacherAssigned($courseId, $teacherId)) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'You are not assigned to this course.'
                        ])->setStatusCode(403);
                    }
                }
                
                $enrollments = $this->enrollmentModel->getPendingEnrollments($courseId);
            } else {
                // Get all pending enrollments for teacher's courses
                if ($userRole === 'teacher') {
                    $teacherId = session('userID');
                    $enrollments = $this->enrollmentModel->getPendingEnrollmentsForTeacher($teacherId);
                } else {
                    // Admin sees all pending enrollments
                    $enrollments = $this->enrollmentModel->where('status', 'pending')
                                                         ->orderBy('created_at', 'DESC')
                                                         ->findAll();
                }
            }
            
            return $this->response->setJSON([
                'success' => true,
                'enrollments' => $enrollments,
                'csrf_token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error getting pending enrollments: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Unenroll a student from a course (Teacher/Admin only)
     */
    public function unenrollStudent()
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
        
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in.'
            ])->setStatusCode(401);
        }
        
        $userRole = strtolower(session('role') ?? '');
        
        // Only teachers and admins can unenroll students
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Teacher/Admin privileges required.'
            ])->setStatusCode(403);
        }
        
        $enrollmentId = $this->request->getPost('enrollment_id');
        
        if (empty($enrollmentId) || !is_numeric($enrollmentId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid enrollment ID provided.'
            ]);
        }
        
        $enrollmentId = (int) $enrollmentId;
        
        try {
            // Get enrollment details
            $enrollment = $this->enrollmentModel->getEnrollmentWithDetails($enrollmentId);
            
            if (!$enrollment) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Enrollment not found.'
                ]);
            }
            
            // If teacher, verify they are assigned to this course
            if ($userRole === 'teacher') {
                $courseTeacherModel = new \App\Models\CourseTeacherModel();
                $teacherId = session('userID');
                
                if (!$courseTeacherModel->isTeacherAssigned($enrollment['course_id'], $teacherId)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'You are not assigned to this course.'
                    ])->setStatusCode(403);
                }
            }
            
            // Get student and course details before deletion for notification
            $studentName = $enrollment['student_name'] ?? 'Student';
            $courseTitle = $enrollment['course_title'] ?? 'Course';
            $studentId = $enrollment['user_id'];
            
            // Unenroll student (delete enrollment)
            $result = $this->enrollmentModel->unenrollStudent($enrollmentId);
            
            if ($result) {
                // Notify student
                $notificationModel = new \App\Models\NotificationModel();
                $notificationModel->createNotification(
                    $studentId,
                    "You have been unenrolled from {$courseTitle}.",
                    'enrollment'
                );
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Student unenrolled successfully.',
                    'csrf_token' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to unenroll student.'
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error unenrolling student: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Check for schedule conflicts when a student enrolls in a course
     * Returns array with 'allowed' (bool) and 'message' (string)
     */
    private function checkStudentScheduleConflict($newCourseId, $studentId)
    {
        $courseModel = new \App\Models\CourseModel();
        $scheduleModel = new \App\Models\CourseScheduleModel();
        $semesterModel = new \App\Models\SemesterModel();
        
        // Get the new course details
        $newCourse = $courseModel->find($newCourseId);
        if (!$newCourse) {
            return ['allowed' => false, 'message' => 'Course not found.'];
        }
        
        // Get student's approved enrollments
        $approvedEnrollments = $this->enrollmentModel->getApprovedEnrollments($studentId);
        
        if (empty($approvedEnrollments)) {
            // No existing enrollments, no conflict possible
            return ['allowed' => true, 'message' => ''];
        }
        
        // Get schedules for the new course
        $newCourseSchedules = $scheduleModel->getSchedulesByCourse($newCourseId);
        
        if (empty($newCourseSchedules)) {
            // New course has no schedule, no conflict possible
            return ['allowed' => true, 'message' => ''];
        }
        
        // Get semester info for the new course
        $newCourseSemester = null;
        if ($newCourse['semester_id']) {
            $newCourseSemester = $semesterModel->find($newCourse['semester_id']);
        }
        
        // Check each existing enrollment for conflicts
        foreach ($approvedEnrollments as $existingEnrollment) {
            $existingCourseId = $existingEnrollment['course_id'];
            
            // Skip if it's the same course (shouldn't happen, but just in case)
            if ($existingCourseId == $newCourseId) {
                continue;
            }
            
            // Get the existing course details
            $existingCourse = $courseModel->find($existingCourseId);
            if (!$existingCourse) {
                continue;
            }
            
            // Check if courses are in the same academic year, semester, and term
            // For conflict, courses must be in the same academic year, semester, and term
            $sameAcademicPeriod = false;
            
            // Check academic year
            if ($newCourse['academic_year_id'] && $existingCourse['academic_year_id']) {
                if ($newCourse['academic_year_id'] != $existingCourse['academic_year_id']) {
                    // Different academic years, no conflict
                    continue;
                }
            } else {
                // If either course doesn't have academic year, skip conflict check
                continue;
            }
            
            // Check semester (must match)
            if ($newCourse['semester_id'] && $existingCourse['semester_id']) {
                if ($newCourse['semester_id'] != $existingCourse['semester_id']) {
                    // Different semesters, no conflict
                    continue;
                }
            } else {
                // If either course doesn't have semester, skip conflict check
                continue;
            }
            
            // Get semester details to check term
            $newCourseSemester = $semesterModel->find($newCourse['semester_id']);
            $existingCourseSemester = $semesterModel->find($existingCourse['semester_id']);
            
            // Check term (must match for conflict)
            if ($newCourseSemester && $existingCourseSemester) {
                if (isset($newCourseSemester['term']) && isset($existingCourseSemester['term'])) {
                    if ($newCourseSemester['term'] != $existingCourseSemester['term']) {
                        // Different terms, no conflict
                        continue;
                    }
                }
            }
            
            // If we reach here, courses are in the same academic year, semester, and term - check for schedule conflicts
            $sameAcademicPeriod = true;
            
            // Get schedules for the existing course
            $existingCourseSchedules = $scheduleModel->getSchedulesByCourse($existingCourseId);
            
            if (empty($existingCourseSchedules)) {
                // Existing course has no schedule, no conflict
                continue;
            }
            
            // Check for overlapping schedules
            foreach ($newCourseSchedules as $newSchedule) {
                foreach ($existingCourseSchedules as $existingSchedule) {
                    // Check if same day
                    if ($newSchedule['day_of_week'] !== $existingSchedule['day_of_week']) {
                        continue;
                    }
                    
                    // Check if times overlap
                    $newStart = strtotime($newSchedule['start_time']);
                    $newEnd = strtotime($newSchedule['end_time']);
                    $existingStart = strtotime($existingSchedule['start_time']);
                    $existingEnd = strtotime($existingSchedule['end_time']);
                    
                    // Check for time overlap
                    if (($newStart < $existingEnd && $newEnd > $existingStart)) {
                        // Conflict found!
                        $existingCourseTitle = $existingCourse['title'] ?? 'Unknown Course';
                        $day = $newSchedule['day_of_week'];
                        $newTime = date('g:i A', $newStart) . ' - ' . date('g:i A', $newEnd);
                        $existingTime = date('g:i A', $existingStart) . ' - ' . date('g:i A', $existingEnd);
                        
                        $semesterInfo = '';
                        if ($newCourseSemester) {
                            $semesterName = $newCourseSemester['name'] ?? '';
                            $semester = $newCourseSemester['semester'] ?? '';
                            $term = $newCourseSemester['term'] ?? '';
                            $semesterInfo = " ({$semesterName} - {$semester} Semester, {$term} Term)";
                        }
                        
                        // Get academic year info
                        $academicYearModel = new \App\Models\AcademicYearModel();
                        $academicYear = $academicYearModel->find($newCourse['academic_year_id']);
                        $academicYearInfo = '';
                        if ($academicYear) {
                            $academicYearInfo = " - Academic Year {$academicYear['year_start']}-{$academicYear['year_end']}";
                        }
                        
                        return [
                            'allowed' => false,
                            'message' => "Schedule conflict detected! You are already enrolled in '{$existingCourseTitle}' with a schedule on {$day} from {$existingTime}{$semesterInfo}{$academicYearInfo}. The course you're trying to enroll in has a conflicting schedule on {$day} from {$newTime}{$semesterInfo}{$academicYearInfo}. Please choose a different course or contact your advisor."
                        ];
                    }
                }
            }
        }
        
        // No conflicts found
        return ['allowed' => true, 'message' => ''];
    }
}
