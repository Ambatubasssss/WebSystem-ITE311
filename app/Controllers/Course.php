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

        // Validate course_id
        if (empty($courseId) || !is_numeric($courseId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID provided.'
            ]);
        }

        // Get course from database
        $course = $this->courseModel->find($courseId);
        
        // Check if course exists
        if (!$course) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        // Check if user is already enrolled using direct database query
        $db = \Config\Database::connect();
        $enrollmentQuery = $db->query("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?", [$userId, $courseId]);
        if ($enrollmentQuery->getNumRows() > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You are already enrolled in this course.'
            ]);
        }

        // Insert enrollment record using direct database query
        try {
            $insertQuery = $db->query("
                INSERT INTO enrollments (user_id, course_id, created_at, updated_at) 
                VALUES (?, ?, NOW(), NOW())
            ", [$userId, $courseId]);
            
            if ($db->affectedRows() > 0) {
                // Create notification for successful enrollment
                $notificationModel = new \App\Models\NotificationModel();
                if ($userRole === 'teacher') {
                    // Get student name for notification
                    $userModel = new \App\Models\UserModel();
                    $student = $userModel->find($userId);
                    $studentName = $student ? $student['name'] : 'Student';
                    $notificationMessage = "You have been enrolled in " . $course['title'] . " by your teacher!";
                } else {
                    $notificationMessage = "You have been enrolled in " . $course['title'] . "!";
                }
                $notificationModel->createNotification($userId, $notificationMessage);
                
                $successMessage = ($userRole === 'teacher') 
                    ? 'Successfully enrolled student in ' . $course['title'] . '!'
                    : 'Successfully enrolled in ' . $course['title'] . '!';
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $successMessage,
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
     */
    public function getAvailableCourses()
    {
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in to view courses.'
            ]);
        }

        $userId = session('userID');
        $courses = $this->courseModel->getCoursesNotEnrolledByUser($userId);

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
     * Display course details (for future use)
     */
    public function view($courseId)
    {
        if (!session()->get('logged_in')) {
            session()->setFlashdata('error', 'You must be logged in to view courses.');
            return redirect()->to('/login');
        }

        $course = $this->courseModel->find($courseId);
        
        if (!$course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/dashboard');
        }

        $data = [
            'course' => $course,
            'title' => $course['title']
        ];

        return view('course/view', $data);
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
}
