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
        // Handle POST request - Process file upload (using strtolower to handle case-insensitive comparison)
        if (strtolower($this->request->getMethod()) === 'post') {
            return $this->handleFileUpload($course_id);
        }
        
        // Handle GET request - Show upload form
        
        // Check if user is admin/teacher
        $userRole = strtolower(session('role') ?? '');
        
        if ($userRole !== 'admin' && $userRole !== 'teacher') {
            session()->setFlashdata('error', 'Access denied. Admin/Teacher privileges required.');
            return redirect()->to('/dashboard');
        }

        // Get course details
        $course = $this->courseModel->find($course_id);
        if (!$course) {
            $redirectPath = ($userRole === 'admin') ? '/dashboard?section=courses' : '/dashboard?section=my-courses';
            return redirect()->to($redirectPath)->with('error', 'Course not found.');
        }

        // Redirect to unified dashboard with upload section
        return redirect()->to('/dashboard?section=upload&course_id=' . $course_id);
    }

    /**
     * Handle file upload process
     */
    private function handleFileUpload($course_id)
    {
        // Validate file upload
        if (!isset($_FILES['material_file']) || $_FILES['material_file']['error'] !== UPLOAD_ERR_OK) {
            return redirect()->back()->with('error', 'File upload failed. Please try again.');
        }
        
        // Setup upload directory
        $uploadPath = WRITEPATH . 'uploads/materials/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Generate unique filename
        $fileName = 'upload_' . time() . '_' . $_FILES['material_file']['name'];
        $targetPath = $uploadPath . $fileName;
        
        // Move uploaded file
        if (!move_uploaded_file($_FILES['material_file']['tmp_name'], $targetPath)) {
            return redirect()->back()->with('error', 'Failed to move uploaded file.');
        }

        // Insert into database
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('materials');
            
            $data = [
                'course_id' => $course_id,
                'file_name' => $_FILES['material_file']['name'],
                'file_path' => 'uploads/materials/' . $fileName,
                'file_size' => $_FILES['material_file']['size'],
                'file_type' => pathinfo($_FILES['material_file']['name'], PATHINFO_EXTENSION),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $builder->insert($data);
            
            if ($result) {
                // Get user role for redirect
                $userRole = strtolower(session('role') ?? '');
                $redirectPath = ($userRole === 'admin') ? '/dashboard?section=upload&course_id=' . $course_id : '/dashboard?section=upload&course_id=' . $course_id;
                
                return redirect()->to($redirectPath)->with('success', 'Material uploaded successfully!');
            } else {
                // Delete uploaded file if database insert fails
                unlink($targetPath);
                return redirect()->back()->with('error', 'Failed to save material to database.');
            }
            
        } catch (\Exception $e) {
            // Delete uploaded file if exception occurs
            if (file_exists($targetPath)) {
                unlink($targetPath);
            }
            log_message('error', 'Material upload exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
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
            // Get course_id from material to redirect back to upload section
            $courseId = $material['course_id'];
            $userRole = strtolower(session('role') ?? '');
            $redirectPath = '/dashboard?section=upload&course_id=' . $courseId;
            return redirect()->to($redirectPath)->with('success', 'Material deleted successfully.');
        } else {
            $courseId = $material['course_id'];
            $redirectPath = '/dashboard?section=upload&course_id=' . $courseId;
            return redirect()->to($redirectPath)->with('error', 'Failed to delete material.');
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
            return redirect()->to('/dashboard')->with('error', 'Material not found.');
        }

        // Check if user is enrolled in the course (skip for admin/teacher)
        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'admin' && $userRole !== 'teacher') {
            if (!$this->enrollmentModel->isAlreadyEnrolled(session('userID'), $material['course_id'])) {
                return redirect()->to('/dashboard')->with('error', 'You are not enrolled in this course.');
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
            return redirect()->to('/dashboard')->with('error', 'File not found.');
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
            return redirect()->to('/dashboard')->with('error', 'Failed to read file.');
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
                return redirect()->to('/dashboard')->with('error', 'You are not enrolled in this course.');
            }
        }

        $course = $this->courseModel->find($course_id);
        if (!$course) {
            return redirect()->to('/dashboard')->with('error', 'Course not found.');
        }

        // Redirect to unified dashboard with materials section
        return redirect()->to('/dashboard?section=materials&course_id=' . $course_id);
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
            return redirect()->to('/dashboard')->with('error', 'Material not found.');
        }

        // Check if user is enrolled in the course (skip for admin/teacher)
        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'admin' && $userRole !== 'teacher') {
            if (!$this->enrollmentModel->isAlreadyEnrolled(session('userID'), $material['course_id'])) {
                return redirect()->to('/dashboard')->with('error', 'You are not enrolled in this course.');
            }
        }

        // Get file path
        $filePath = WRITEPATH . $material['file_path'];
        
        if (!file_exists($filePath)) {
            return redirect()->to('/dashboard')->with('error', 'File not found.');
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
