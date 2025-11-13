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

        // Redirect to unified dashboard with enrollments section
        return redirect()->to('/dashboard?section=enrollments');
    }

    public function assignments()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'student') {
            session()->setFlashdata('error', 'Access denied. Student role required.');
            return redirect()->to('/dashboard');
        }

        // Redirect to unified dashboard with assignments section
        return redirect()->to('/dashboard?section=assignments');
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

        // Redirect to unified dashboard with materials section
        return redirect()->to('/dashboard?section=materials&course_id=' . $course_id);
    }
}
