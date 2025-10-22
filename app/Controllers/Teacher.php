<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Teacher extends BaseController
{
    public function dashboard()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'teacher') {
            session()->setFlashdata('error', 'Access denied. Teacher role required.');
            return redirect()->to('/announcements');
        }

        // Load models
        $courseModel = new \App\Models\CourseModel();
        $materialModel = new \App\Models\MaterialModel();

        // Get all courses
        $courses = $courseModel->findAll();
        
        // Get material counts for each course
        $coursesWithMaterials = [];
        foreach ($courses as $course) {
            $materialCount = $materialModel->where('course_id', $course['id'])->countAllResults();
            $course['material_count'] = $materialCount;
            $coursesWithMaterials[] = $course;
        }

        $data = [
            'title' => 'Teacher Dashboard',
            'courses' => $coursesWithMaterials
        ];

        return view('teacher_dashboard', $data);
    }

    public function courses()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'teacher') {
            session()->setFlashdata('error', 'Access denied. Teacher role required.');
            return redirect()->to('/dashboard');
        }

        $data = [
            'courses' => ['Math 101', 'Science 202', 'Physics 301'],
            'title' => 'My Courses'
        ];

        return view('teacher/courses', $data);
    }

    public function grades()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'teacher') {
            session()->setFlashdata('error', 'Access denied. Teacher role required.');
            return redirect()->to('/dashboard');
        }

        $data = [
            'assignments' => [
                ['student' => 'John Doe', 'assignment' => 'Math Quiz 1', 'grade' => 'A'],
                ['student' => 'Jane Smith', 'assignment' => 'Math Quiz 1', 'grade' => 'B+']
            ],
            'title' => 'Grade Management'
        ];

        return view('teacher/grades', $data);
    }
}
