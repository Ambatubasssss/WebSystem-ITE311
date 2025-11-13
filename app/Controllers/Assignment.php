<?php

namespace App\Controllers;

use App\Models\AssignmentModel;
use App\Models\AssignmentSubmissionModel;
use App\Models\CourseModel;
use App\Models\EnrollmentModel;
use App\Models\NotificationModel;

class Assignment extends BaseController
{
    protected $assignmentModel;
    protected $submissionModel;
    protected $courseModel;
    protected $enrollmentModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->assignmentModel = new AssignmentModel();
        $this->submissionModel = new AssignmentSubmissionModel();
        $this->courseModel = new CourseModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->notificationModel = new NotificationModel();
        
        helper('form');
    }

    /**
     * Create a new assignment (Teacher/Admin only)
     */
    public function create()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Teacher/Admin privileges required.');
            return redirect()->to('/dashboard');
        }

        if (strtolower($this->request->getMethod()) === 'post') {
            $courseId = $this->request->getPost('course_id');
            $title = $this->request->getPost('title');
            $description = $this->request->getPost('description');
            $dueDate = $this->request->getPost('due_date');
            $totalPoints = $this->request->getPost('total_points') ?? 100;
            $assignmentType = $this->request->getPost('assignment_type') ?? 'file';

            // Validate course exists
            $course = $this->courseModel->find($courseId);
            if (!$course) {
                return redirect()->back()->with('error', 'Course not found.');
            }

            $data = [
                'course_id' => $courseId,
                'title' => $title,
                'description' => $description,
                'due_date' => $dueDate ? date('Y-m-d H:i:s', strtotime($dueDate)) : null,
                'total_points' => $totalPoints,
                'assignment_type' => $assignmentType,
                'created_by' => session('userID')
            ];

            if ($this->assignmentModel->insert($data)) {
                // Notify enrolled students
                $enrolledStudents = $this->enrollmentModel->where('course_id', $courseId)->findAll();
                foreach ($enrolledStudents as $enrollment) {
                    $this->notificationModel->insert([
                        'user_id' => $enrollment['user_id'],
                        'message' => "New assignment: {$title} in {$course['title']}",
                        'type' => 'assignment',
                        'is_read' => 0
                    ]);
                }

                return redirect()->to('/dashboard?section=assignments&course_id=' . $courseId)->with('success', 'Assignment created successfully!');
            } else {
                return redirect()->back()->with('error', 'Failed to create assignment.');
            }
        }

        // GET request - redirect to dashboard
        return redirect()->to('/dashboard?section=create-assignment');
    }

    /**
     * View assignment details
     */
    public function view($assignment_id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $assignment = $this->assignmentModel->getAssignmentWithDetails($assignment_id);
        if (!$assignment) {
            return redirect()->to('/dashboard')->with('error', 'Assignment not found.');
        }

        $userRole = strtolower(session('role') ?? '');
        $userId = session('userID');

        // Check access
        if ($userRole === 'student') {
            // Check if student is enrolled
            if (!$this->enrollmentModel->isAlreadyEnrolled($userId, $assignment['course_id'])) {
                return redirect()->to('/dashboard')->with('error', 'You are not enrolled in this course.');
            }
        } elseif ($userRole !== 'teacher' && $userRole !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        // Get submission if student
        $submission = null;
        if ($userRole === 'student') {
            $submission = $this->submissionModel->getSubmissionByUserAndAssignment($userId, $assignment_id);
        }

        // Get all submissions if teacher/admin
        $submissions = [];
        if ($userRole === 'teacher' || $userRole === 'admin') {
            $submissions = $this->submissionModel->getSubmissionsByAssignment($assignment_id);
        }

        return redirect()->to('/dashboard?section=view-assignment&assignment_id=' . $assignment_id);
    }

    /**
     * Submit assignment (Student only)
     */
    public function submit()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'student') {
            session()->setFlashdata('error', 'Only students can submit assignments.');
            return redirect()->to('/dashboard');
        }

        $assignmentId = $this->request->getPost('assignment_id');
        $assignment = $this->assignmentModel->getAssignmentWithDetails($assignmentId);
        
        if (!$assignment) {
            return redirect()->back()->with('error', 'Assignment not found.');
        }

        // Check if student is enrolled
        $userId = session('userID');
        if (!$this->enrollmentModel->isAlreadyEnrolled($userId, $assignment['course_id'])) {
            return redirect()->back()->with('error', 'You are not enrolled in this course.');
        }

        // Check if already submitted
        if ($this->submissionModel->hasUserSubmitted($userId, $assignmentId)) {
            return redirect()->back()->with('error', 'You have already submitted this assignment.');
        }

        // Get assignment type
        $assignmentType = $assignment['assignment_type'] ?? 'file';
        $submissionType = $assignmentType;
        $textEntry = null;
        $fileName = null;
        $filePath = null;
        $fileSize = null;
        $fileType = null;

        // Handle based on assignment type
        if ($assignmentType === 'text') {
            // Text entry submission
            $textEntry = $this->request->getPost('text_entry');
            if (empty($textEntry)) {
                return redirect()->back()->with('error', 'Text entry is required.');
            }
        } else {
            // File upload submission
            if (!isset($_FILES['submission_file']) || $_FILES['submission_file']['error'] !== UPLOAD_ERR_OK) {
                return redirect()->back()->with('error', 'File upload failed. Please try again.');
            }

            // Setup upload directory
            $uploadPath = WRITEPATH . 'uploads/assignments/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Generate unique filename
            $fileName = 'submission_' . time() . '_' . $userId . '_' . $_FILES['submission_file']['name'];
            $targetPath = $uploadPath . $fileName;

            // Move uploaded file
            if (!move_uploaded_file($_FILES['submission_file']['tmp_name'], $targetPath)) {
                return redirect()->back()->with('error', 'Failed to move uploaded file.');
            }

            $filePath = 'uploads/assignments/' . $fileName;
            $fileSize = $_FILES['submission_file']['size'];
            $fileType = pathinfo($_FILES['submission_file']['name'], PATHINFO_EXTENSION);
            $fileName = $_FILES['submission_file']['name'];
        }

        // Insert into database
        try {
            $data = [
                'assignment_id' => $assignmentId,
                'user_id' => $userId,
                'submission_type' => $submissionType,
                'submitted_at' => date('Y-m-d H:i:s')
            ];
            
            // Add text or file data based on submission type
            if ($assignmentType === 'text') {
                $data['text_entry'] = $textEntry;
                // Leave file fields as null for text submissions
            } else {
                $data['file_name'] = $fileName;
                $data['file_path'] = $filePath;
                $data['file_size'] = $fileSize;
                $data['file_type'] = $fileType;
                // Leave text_entry as null for file submissions
            }

            $insertResult = $this->submissionModel->insert($data);
            
            if ($insertResult) {
                // Notify teacher
                $this->notificationModel->insert([
                    'user_id' => $assignment['created_by'],
                    'message' => "New submission for assignment: {$assignment['title']} from " . session('name'),
                    'type' => 'submission',
                    'is_read' => 0
                ]);

                return redirect()->to('/dashboard?section=view-assignment&assignment_id=' . $assignmentId)->with('success', 'Assignment submitted successfully!');
            } else {
                // Get validation errors if any
                $errors = $this->submissionModel->errors();
                $errorMessage = 'Failed to save submission to database.';
                if (!empty($errors)) {
                    $errorMessage .= ' Errors: ' . implode(', ', $errors);
                    log_message('error', 'Assignment submission validation errors: ' . json_encode($errors));
                }
                
                // Delete uploaded file if database insert fails (only for file submissions)
                if ($assignmentType === 'file' && isset($targetPath) && file_exists($targetPath)) {
                    unlink($targetPath);
                }
                
                log_message('error', 'Assignment submission failed. Data: ' . json_encode($data));
                return redirect()->back()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            // Delete uploaded file if exception occurs (only for file submissions)
            if ($assignmentType === 'file' && isset($targetPath) && file_exists($targetPath)) {
                unlink($targetPath);
            }
            log_message('error', 'Assignment submission exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Download submission file (Teacher/Admin only)
     */
    public function downloadSubmission($submission_id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            session()->setFlashdata('error', 'Access denied.');
            return redirect()->to('/dashboard');
        }

        $submission = $this->submissionModel->getSubmissionWithDetails($submission_id);
        if (!$submission) {
            return redirect()->to('/dashboard')->with('error', 'Submission not found.');
        }

        $filePath = WRITEPATH . $submission['file_path'];
        
        if (!file_exists($filePath)) {
            return redirect()->to('/dashboard')->with('error', 'File not found.');
        }

        // Set headers for download
        $this->response->setHeader('Content-Type', 'application/octet-stream');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $submission['file_name'] . '"');
        $this->response->setHeader('Content-Length', filesize($filePath));
        
        return $this->response->download($filePath, null);
    }

    /**
     * View submission file (Teacher/Admin only)
     */
    public function viewSubmission($submission_id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            session()->setFlashdata('error', 'Access denied.');
            return redirect()->to('/dashboard');
        }

        $submission = $this->submissionModel->getSubmissionWithDetails($submission_id);
        if (!$submission) {
            return redirect()->to('/dashboard')->with('error', 'Submission not found.');
        }

        $filePath = WRITEPATH . $submission['file_path'];
        
        if (!file_exists($filePath)) {
            return redirect()->to('/dashboard')->with('error', 'File not found.');
        }

        // Determine content type
        $fileType = strtolower($submission['file_type']);
        $contentType = 'application/octet-stream';
        
        if ($fileType === 'pdf') {
            $contentType = 'application/pdf';
        } elseif (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $contentType = 'image/' . $fileType;
        } elseif ($fileType === 'txt') {
            $contentType = 'text/plain';
        }

        $this->response->setHeader('Content-Type', $contentType);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . $submission['file_name'] . '"');
        
        return $this->response->download($filePath, null);
    }

    /**
     * Grade assignment submission (Teacher/Admin only)
     */
    public function grade()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            session()->setFlashdata('error', 'Access denied.');
            return redirect()->to('/dashboard');
        }

        $submissionId = $this->request->getPost('submission_id');
        $assignmentId = $this->request->getPost('assignment_id');
        $score = $this->request->getPost('score');
        $feedback = $this->request->getPost('feedback');

        // Validate submission exists
        $submission = $this->submissionModel->find($submissionId);
        if (!$submission) {
            return redirect()->back()->with('error', 'Submission not found.');
        }

        // Validate assignment exists
        $assignment = $this->assignmentModel->find($assignmentId);
        if (!$assignment) {
            return redirect()->back()->with('error', 'Assignment not found.');
        }

        // Validate score
        if ($score === null || $score === '') {
            return redirect()->back()->with('error', 'Score is required.');
        }

        $score = floatval($score);
        if ($score < 0 || $score > $assignment['total_points']) {
            return redirect()->back()->with('error', 'Score must be between 0 and ' . $assignment['total_points']);
        }

        // Update submission with grade
        $data = [
            'score' => $score,
            'feedback' => $feedback ?: null,
            'graded_at' => date('Y-m-d H:i:s'),
            'graded_by' => session('userID')
        ];

        if ($this->submissionModel->update($submissionId, $data)) {
            // Notify student
            $this->notificationModel->insert([
                'user_id' => $submission['user_id'],
                'message' => "Your assignment '{$assignment['title']}' has been graded. Score: {$score}/{$assignment['total_points']}",
                'type' => 'grade',
                'is_read' => 0
            ]);

            return redirect()->to('/dashboard?section=view-assignment&assignment_id=' . $assignmentId)->with('success', 'Grade saved successfully!');
        } else {
            $errors = $this->submissionModel->errors();
            $errorMessage = 'Failed to save grade.';
            if (!empty($errors)) {
                $errorMessage .= ' Errors: ' . implode(', ', $errors);
            }
            return redirect()->back()->with('error', $errorMessage);
        }
    }

    /**
     * Delete assignment (Teacher/Admin only) - but NOT submissions
     */
    public function delete($assignment_id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $userRole = strtolower(session('role') ?? '');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            session()->setFlashdata('error', 'Access denied.');
            return redirect()->to('/dashboard');
        }

        $assignment = $this->assignmentModel->getAssignmentWithDetails($assignment_id);
        if (!$assignment) {
            return redirect()->back()->with('error', 'Assignment not found.');
        }

        // Check if teacher created this assignment (or is admin)
        if ($userRole === 'teacher' && $assignment['created_by'] != session('userID')) {
            return redirect()->back()->with('error', 'You can only delete your own assignments.');
        }

        // Delete assignment (submissions will remain due to CASCADE or we can keep them)
        if ($this->assignmentModel->delete($assignment_id)) {
            return redirect()->to('/dashboard?section=assignments&course_id=' . $assignment['course_id'])->with('success', 'Assignment deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to delete assignment.');
        }
    }
}
