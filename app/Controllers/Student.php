<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Student extends BaseController
{
    public function enrollments()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'student') {
            session()->setFlashdata('error', 'Access denied. Student role required.');
            return redirect()->to('/dashboard');
        }

        // Redirect to unified dashboard with enrollment data
        session()->setFlashdata('show_enrollments', true);
        return redirect()->to('/dashboard');
    }

    public function assignments()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'student') {
            session()->setFlashdata('error', 'Access denied. Student role required.');
            return redirect()->to('/dashboard');
        }

        // Redirect to unified dashboard with assignment data
        session()->setFlashdata('show_assignments', true);
        return redirect()->to('/dashboard');
    }

    public function materials($course_id)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'student') {
            session()->setFlashdata('error', 'Access denied. Student role required.');
            return redirect()->to('/dashboard');
        }

        // Check if user is enrolled in the course
        $enrollmentModel = new \App\Models\EnrollmentModel();
        if (!$enrollmentModel->isAlreadyEnrolled(session('userID'), $course_id)) {
            session()->setFlashdata('error', 'You are not enrolled in this course.');
            return redirect()->to('/dashboard');
        }

        // Get course and materials
        $courseModel = new \App\Models\CourseModel();
        $materialModel = new \App\Models\MaterialModel();
        
        $course = $courseModel->find($course_id);
        if (!$course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/dashboard');
        }

        $data = [
            'title' => 'Course Materials',
            'course' => $course,
            'materials' => $materialModel->getMaterialsByCourse($course_id)
        ];

        return view('student/course_materials', $data);
    }
}
