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
        
        // Load helpers
        helper('form');
    }

    /**
     * Display file upload form and handle file upload
     */
    public function upload($course_id)
    {
        // Debug: Log that upload method is called
        log_message('info', 'Upload method called for course ID: ' . $course_id);
        log_message('info', 'Request method: ' . $this->request->getMethod());
        
        // Check if user is admin/teacher
        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'admin' && $userRole !== 'teacher') {
            session()->setFlashdata('error', 'Access denied. Admin/Teacher privileges required.');
            return redirect()->to('/dashboard');
        }

        // Get course details
        $course = $this->courseModel->find($course_id);
        if (!$course) {
            $redirectPath = ($userRole === 'admin') ? '/admin/courses' : '/teacher/dashboard';
            return redirect()->to($redirectPath)->with('error', 'Course not found.');
        }

        if ($this->request->getMethod() === 'post') {
            log_message('info', 'POST request detected, calling handleFileUpload');
            return $this->handleFileUpload($course_id);
        } else {
            log_message('info', 'GET request detected, showing upload form');
        }

        // Get materials for the course
        $materials = $this->materialModel->getMaterialsByCourse($course_id);
        
        // Debug: Log the materials count
        log_message('info', 'Course ID: ' . $course_id . ', Materials count: ' . count($materials));
        
        // Display upload form
        $data = [
            'title' => 'Upload Course Material',
            'course' => $course,
            'materials' => $materials
        ];

        return view('admin/upload_material', $data);
    }

    /**
     * Handle file upload process - WORKING VERSION
     */
    private function handleFileUpload($course_id)
    {
        $file = $this->request->getFile('material_file');

        // Basic validation
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'No file selected or file is invalid.');
        }

        // Create upload directory
        $uploadPath = WRITEPATH . 'uploads/materials/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Move file
        $newName = $file->getRandomName();
        $moved = $file->move($uploadPath, $newName);

        if (!$moved) {
            return redirect()->back()->with('error', 'Failed to move uploaded file.');
        }

        // Prepare database data
        $data = [
            'course_id' => $course_id,
            'file_name' => $file->getClientName(),
            'file_path' => 'uploads/materials/' . $newName,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientExtension()
        ];

        // Insert into database
        $result = $this->materialModel->insert($data);
        
        if ($result) {
            return redirect()->back()->with('success', 'Material uploaded successfully!');
        } else {
            // Clean up file if database insert failed
            if (file_exists($uploadPath . $newName)) {
                unlink($uploadPath . $newName);
            }
            $errors = $this->materialModel->errors();
            return redirect()->back()->with('error', 'Failed to save to database: ' . implode(', ', $errors));
        }
    }

    /**
     * Delete a material
     */
    public function delete($material_id)
    {
        // Check if user is admin/teacher
        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'admin' && $userRole !== 'teacher') {
            session()->setFlashdata('error', 'Access denied.');
            return redirect()->to('/dashboard');
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
     * Download a material file
     */
    public function download($material_id)
    {
        // Check if user is logged in
        if (!session('userID')) {
            return redirect()->to('/login')->with('error', 'Please login to download materials.');
        }

        // Get material details
        $material = $this->materialModel->getMaterialWithCourse($material_id);
        if (!$material) {
            return redirect()->to('/')->with('error', 'Material not found.');
        }

        // Check if user is enrolled in the course (skip for admin/teacher)
        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'admin' && $userRole !== 'teacher') {
            if (!$this->enrollmentModel->isAlreadyEnrolled(session('userID'), $material['course_id'])) {
                return redirect()->to('/')->with('error', 'You are not enrolled in this course.');
            }
        }

        // Get file path
        $filePath = WRITEPATH . $material['file_path'];
        
        // Debug: Log the file path and check
        log_message('info', 'Download attempt - Material ID: ' . $material_id);
        log_message('info', 'File path: ' . $filePath);
        log_message('info', 'File exists: ' . (file_exists($filePath) ? 'YES' : 'NO'));
        log_message('info', 'File size: ' . (file_exists($filePath) ? filesize($filePath) : 'N/A'));
        
        if (!file_exists($filePath)) {
            log_message('error', 'File not found at: ' . $filePath);
            return redirect()->to('/')->with('error', 'File not found.');
        }

        // Force download using custom method
        log_message('info', 'Attempting download of: ' . $material['file_name']);
        
        // Set headers for download
        $this->response->setHeader('Content-Type', 'application/octet-stream');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $material['file_name'] . '"');
        $this->response->setHeader('Content-Length', filesize($filePath));
        $this->response->setHeader('Cache-Control', 'no-cache, must-revalidate');
        $this->response->setHeader('Pragma', 'no-cache');
        
        // Read and output file
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            log_message('error', 'Failed to read file content');
            return redirect()->to('/')->with('error', 'Failed to read file.');
        }
        
        return $this->response->setBody($fileContent);
    }

    /**
     * View materials for a specific course (student view)
     */
    public function view($course_id)
    {
        // Check if user is logged in
        if (!session('userID')) {
            return redirect()->to('/login')->with('error', 'Please login to view materials.');
        }

        // Check if user is enrolled in the course (skip for admin/teacher)
        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'admin' && $userRole !== 'teacher') {
            if (!$this->enrollmentModel->isAlreadyEnrolled(session('userID'), $course_id)) {
                return redirect()->to('/')->with('error', 'You are not enrolled in this course.');
            }
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

    /**
     * View a material file in browser (no download)
     */
    public function viewFile($material_id)
    {
        // Check if user is logged in
        if (!session('userID')) {
            return redirect()->to('/login')->with('error', 'Please login to view materials.');
        }

        // Get material details
        $material = $this->materialModel->getMaterialWithCourse($material_id);
        if (!$material) {
            return redirect()->to('/')->with('error', 'Material not found.');
        }

        // Check if user is enrolled in the course (skip for admin/teacher)
        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'admin' && $userRole !== 'teacher') {
            if (!$this->enrollmentModel->isAlreadyEnrolled(session('userID'), $material['course_id'])) {
                return redirect()->to('/')->with('error', 'You are not enrolled in this course.');
            }
        }

        // Get file path
        $filePath = WRITEPATH . $material['file_path'];
        
        if (!file_exists($filePath)) {
            return redirect()->to('/')->with('error', 'File not found.');
        }

        // Get file info
        $fileSize = filesize($filePath);
        $fileType = mime_content_type($filePath);
        
        // Set appropriate headers for viewing
        $this->response->setHeader('Content-Type', $fileType);
        $this->response->setHeader('Content-Length', $fileSize);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . $material['file_name'] . '"');
        
        // Output file content
        return $this->response->setBody(file_get_contents($filePath));
    }

}
