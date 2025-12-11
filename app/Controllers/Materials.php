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
     * Handle file upload process using CodeIgniter's File Uploading Library
     */
    private function handleFileUpload($course_id)
    {
        // Get the uploaded file using CodeIgniter's File Uploading Library
        $file = $this->request->getFile('material_file');
        
        // Check if file was uploaded
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File upload failed. Please select a valid file.');
        }
        
        // Setup upload directory
        $uploadPath = WRITEPATH . 'uploads/materials/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Define allowed file types and MIME types for security
        // Only allow PPT and PDF files for assignment materials
        $allowedExtensions = ['pdf', 'ppt', 'pptx'];
        $allowedMimeTypes = [
            'application/pdf',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];
        
        // Get file extension and validate
        $originalName = $file->getName();
        $extension = strtolower($file->getClientExtension());
        
        // Security: Validate file extension
        if (!in_array($extension, $allowedExtensions)) {
            return redirect()->back()->with('error', 'Invalid file type. Only PDF and PowerPoint (PPT, PPTX) files are allowed.');
        }
        
        // Security: Validate MIME type to prevent file type spoofing
        $mimeType = $file->getClientMimeType();
        if (!in_array($mimeType, $allowedMimeTypes)) {
            return redirect()->back()->with('error', 'Invalid file type detected. Only PDF and PowerPoint files are allowed.');
        }
        
        // Security: Validate file size (10MB = 10485760 bytes)
        $maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if ($file->getSize() > $maxSize) {
            return redirect()->back()->with('error', 'File size exceeds the maximum limit of 10MB.');
        }
        
        // Security: Sanitize filename to prevent path traversal attacks
        $sanitizedOriginalName = basename($originalName); // Remove any path components
        $sanitizedOriginalName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $sanitizedOriginalName); // Remove special characters
        $sanitizedOriginalName = preg_replace('/_{2,}/', '_', $sanitizedOriginalName); // Replace multiple underscores
        
        // Generate unique filename with timestamp to prevent overwrites
        $uniqueFileName = 'upload_' . time() . '_' . uniqid() . '_' . $sanitizedOriginalName;
        
        // Configure CodeIgniter's Upload Library
        $config = [
            'upload_path'   => $uploadPath,
            'allowed_types' => implode('|', $allowedExtensions),
            'max_size'      => 10240, // 10MB in KB
            'file_name'     => $uniqueFileName,
            'overwrite'     => false
        ];
        
        // Load and configure the Upload Library
        $upload = \Config\Services::upload();
        $upload->initialize($config);
        
        // Perform the upload using CodeIgniter's library
        if (!$upload->doUpload('material_file')) {
            $errors = $upload->getErrors();
            $errorMessage = !empty($errors) ? implode(', ', $errors) : 'File upload failed. Please try again.';
            return redirect()->back()->with('error', $errorMessage);
        }
        
        // Get upload data
        $uploadData = $upload->data();
        
        // Insert into database using model method
        try {
            $data = [
                'course_id' => $course_id,
                'file_name' => $sanitizedOriginalName, // Store original sanitized name
                'file_path' => 'uploads/materials/' . $uploadData['file_name'],
                'file_size' => $uploadData['file_size'],
                'file_type' => $extension
            ];
            
            $result = $this->materialModel->insertMaterial($data);
            
            if ($result) {
                // Get course information for notifications
                $course = $this->courseModel->find($course_id);
                $uploaderName = session('name') ?? 'Teacher';
                $materialName = $sanitizedOriginalName;
                
                // Send notifications
                $notificationModel = new \App\Models\NotificationModel();
                $userModel = new \App\Models\UserModel();
                
                // 1. Notify all admins
                $admins = $userModel->where('role', 'admin')->findAll();
                foreach ($admins as $admin) {
                    $notificationModel->insert([
                        'user_id' => $admin['id'],
                        'message' => "{$uploaderName} uploaded a new material '{$materialName}' to the course '{$course['title']}'.",
                        'type' => 'material',
                        'is_read' => 0
                    ]);
                }
                
                // 2. Notify all enrolled students in the course
                $enrolledStudents = $this->enrollmentModel->where('course_id', $course_id)
                                                          ->where('status', 'approved')
                                                          ->findAll();
                
                foreach ($enrolledStudents as $enrollment) {
                    $notificationModel->insert([
                        'user_id' => $enrollment['user_id'],
                        'message' => "New material '{$materialName}' has been uploaded to the course '{$course['title']}'.",
                        'type' => 'material',
                        'is_read' => 0
                    ]);
                }
                
                // Get user role for redirect
                $userRole = strtolower(session('role') ?? '');
                $redirectPath = '/dashboard?section=upload&course_id=' . $course_id;
                
                return redirect()->to($redirectPath)->with('success', 'Material uploaded successfully!');
            } else {
                // Delete uploaded file if database insert fails
                $filePath = $uploadPath . $uploadData['file_name'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                return redirect()->back()->with('error', 'Failed to save material to database.');
            }
            
        } catch (\Exception $e) {
            // Delete uploaded file if exception occurs
            $filePath = $uploadPath . $uploadData['file_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
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
            if (!$this->enrollmentModel->isApprovedEnrolled(session('userID'), $material['course_id'])) {
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
            if (!$this->enrollmentModel->isApprovedEnrolled(session('userID'), $course_id)) {
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
            if (!$this->enrollmentModel->isApprovedEnrolled(session('userID'), $material['course_id'])) {
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
