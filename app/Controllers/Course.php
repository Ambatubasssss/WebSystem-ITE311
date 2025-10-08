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

        // Check if user is a student (only students can enroll)
        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'student') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Only students can enroll in courses.'
            ]);
        }

        // Get course_id from POST request
        $courseId = $this->request->getPost('course_id');
        $userId = session('userID');

        // Validate course_id
        if (empty($courseId) || !is_numeric($courseId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID provided.'
            ]);
        }

        // Check if course exists
        $course = $this->courseModel->find($courseId);
        if (!$course) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        // Check if user is already enrolled
        if ($this->enrollmentModel->isAlreadyEnrolled($userId, $courseId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You are already enrolled in this course.'
            ]);
        }

        // Prepare enrollment data
        $enrollmentData = [
            'user_id' => $userId,
            'course_id' => $courseId,
            'enrollment_date' => date('Y-m-d H:i:s')
        ];

        // Insert enrollment record
        try {
            $result = $this->enrollmentModel->enrollUser($enrollmentData);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Successfully enrolled in ' . $course['title'] . '!',
                    'course' => [
                        'id' => $course['id'],
                        'title' => $course['title'],
                        'description' => $course['description']
                    ]
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
}
