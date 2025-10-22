<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\CourseModel;
use App\Models\EnrollmentModel;

class Materials extends BaseController
{
    protected $materialModel;
    protected $courseModel;
    protected $enrollmentModel;

    public function __construct()
    {
        $this->materialModel = new MaterialModel();
        $this->courseModel = new CourseModel();
        $this->enrollmentModel = new EnrollmentModel();
    }

    /**
     * Display file upload form and handle file upload
     */
    public function upload($course_id)
    {
        // Check if user is admin/teacher
        if (session('role') !== 'admin' && session('role') !== 'teacher') {
            return redirect()->to('/')->with('error', 'Access denied. Admin/Teacher privileges required.');
        }

        // Get course details
        $course = $this->courseModel->find($course_id);
        if (!$course) {
            return redirect()->to('/admin/courses')->with('error', 'Course not found.');
        }

        if ($this->request->getMethod() === 'post') {
            return $this->handleFileUpload($course_id);
        }

        // Display upload form
        $data = [
            'title' => 'Upload Course Material',
            'course' => $course,
            'materials' => $this->materialModel->getMaterialsByCourse($course_id)
        ];

        return view('admin/upload_material', $data);
    }

    /**
     * Handle file upload process
     */
    private function handleFileUpload($course_id)
    {
        $validation = \Config\Services::validation();
        $file = $this->request->getFile('material_file');

        if (!$file->isValid()) {
            return redirect()->back()->with('error', 'No file selected or file upload failed.');
        }

        // Configure upload preferences
        $uploadPath = WRITEPATH . 'uploads/materials/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $allowedTypes = 'pdf|doc|docx|ppt|pptx|txt|jpg|jpeg|png|gif';
        $maxSize = 10 * 1024 * 1024; // 10MB

        $file->setValidationRules([
            'material_file' => [
                'rules' => "uploaded[material_file]|max_size[material_file,10240]|ext_in[material_file,$allowedTypes]",
                'errors' => [
                    'uploaded' => 'Please select a file to upload.',
                    'max_size' => 'File size must not exceed 10MB.',
                    'ext_in' => 'Only PDF, DOC, DOCX, PPT, PPTX, TXT, JPG, JPEG, PNG, and GIF files are allowed.'
                ]
            ]
        ]);

        if (!$file->hasMoved()) {
            if ($file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);

                // Prepare data for database
                $materialData = [
                    'course_id' => $course_id,
                    'file_name' => $file->getClientName(),
                    'file_path' => 'uploads/materials/' . $newName,
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getClientExtension()
                ];

                if ($this->materialModel->insertMaterial($materialData)) {
                    return redirect()->back()->with('success', 'Material uploaded successfully.');
                } else {
                    // Delete uploaded file if database insert fails
                    unlink($uploadPath . $newName);
                    return redirect()->back()->with('error', 'Failed to save material information.');
                }
            } else {
                return redirect()->back()->with('error', 'File upload failed: ' . $file->getErrorString());
            }
        }

        return redirect()->back()->with('error', 'File upload failed.');
    }

    /**
     * Delete a material
     */
    public function delete($material_id)
    {
        // Check if user is admin/teacher
        if (session('role') !== 'admin' && session('role') !== 'teacher') {
            return redirect()->to('/')->with('error', 'Access denied.');
        }

        $material = $this->materialModel->find($material_id);
        if (!$material) {
            return redirect()->back()->with('error', 'Material not found.');
        }

        // Delete file from filesystem
        $filePath = WRITEPATH . $material['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete from database
        if ($this->materialModel->delete($material_id)) {
            return redirect()->back()->with('success', 'Material deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to delete material.');
        }
    }

    /**
     * Download a material (for enrolled students only)
     */
    public function download($material_id)
    {
        // Check if user is logged in
        if (!session('user_id')) {
            return redirect()->to('/auth/login')->with('error', 'Please login to download materials.');
        }

        // Check if user is enrolled in the course
        if (!$this->materialModel->isUserEnrolledInMaterialCourse(session('user_id'), $material_id)) {
            return redirect()->to('/')->with('error', 'You are not enrolled in this course.');
        }

        $material = $this->materialModel->find($material_id);
        if (!$material) {
            return redirect()->to('/')->with('error', 'Material not found.');
        }

        $filePath = WRITEPATH . $material['file_path'];
        if (!file_exists($filePath)) {
            return redirect()->to('/')->with('error', 'File not found on server.');
        }

        // Force download
        return $this->response->download($filePath, null);
    }

    /**
     * View materials for a specific course (student view)
     */
    public function view($course_id)
    {
        // Check if user is logged in
        if (!session('user_id')) {
            return redirect()->to('/auth/login')->with('error', 'Please login to view materials.');
        }

        // Check if user is enrolled in the course
        if (!$this->enrollmentModel->isAlreadyEnrolled(session('user_id'), $course_id)) {
            return redirect()->to('/')->with('error', 'You are not enrolled in this course.');
        }

        $course = $this->courseModel->find($course_id);
        if (!$course) {
            return redirect()->to('/')->with('error', 'Course not found.');
        }

        $data = [
            'title' => 'Course Materials',
            'course' => $course,
            'materials' => $this->materialModel->getMaterialsByCourse($course_id)
        ];

        return view('student/course_materials', $data);
    }
}
