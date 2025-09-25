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

        $data = [
            'enrollments' => [
                ['course' => 'History 101', 'instructor' => 'Dr. Smith', 'status' => 'Active'],
                ['course' => 'Art 303', 'instructor' => 'Prof. Johnson', 'status' => 'Active']
            ],
            'title' => 'My Enrollments'
        ];

        return view('student/enrollments', $data);
    }

    public function assignments()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'student') {
            session()->setFlashdata('error', 'Access denied. Student role required.');
            return redirect()->to('/dashboard');
        }

        $data = [
            'assignments' => [
                ['title' => 'Assignment 1', 'course' => 'History 101', 'due_date' => '2024-10-01', 'status' => 'Pending'],
                ['title' => 'Quiz 2', 'course' => 'Art 303', 'due_date' => '2024-10-05', 'status' => 'Completed']
            ],
            'title' => 'My Assignments'
        ];

        return view('student/assignments', $data);
    }
}
