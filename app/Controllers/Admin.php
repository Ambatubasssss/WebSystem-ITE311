<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Admin extends BaseController
{
    public function dashboard()
    {
        // Redirect to unified dashboard
        return redirect()->to('/dashboard');
    }

    public function users()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard');
        }

        // Redirect to unified dashboard with users section
        return redirect()->to('/dashboard?section=users');
    }

    public function settings()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard');
        }

        // Redirect to unified dashboard (settings can be added later)
        return redirect()->to('/dashboard');
    }

    public function courses()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard');
        }

        // Redirect to unified dashboard with courses section
        return redirect()->to('/dashboard?section=courses');
    }

    public function getUsers()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        $userModel = new \App\Models\UserModel();
        $users = $userModel->findAll();

        return $this->response->setJSON([
            'success' => true,
            'users' => $users
        ]);
    }

    public function createUser()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        // Set validation rules
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => [
                'label' => 'Name',
                'rules' => 'required|min_length[3]|max_length[100]|alpha_space',
                'errors' => [
                    'required' => 'The {field} field is required.',
                    'min_length' => 'The {field} must be at least {param} characters long.',
                    'max_length' => 'The {field} cannot exceed {param} characters.',
                    'alpha_space' => 'The {field} can only contain letters and spaces.'
                ]
            ],
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email|max_length[255]|is_unique[users.email]',
                'errors' => [
                    'required' => 'The {field} field is required.',
                    'valid_email' => 'Please provide a valid email address.',
                    'max_length' => 'The {field} cannot exceed {param} characters.',
                    'is_unique' => 'This email address is already registered.'
                ]
            ],
            'role' => [
                'label' => 'Role',
                'rules' => 'required|in_list[admin,teacher,student]',
                'errors' => [
                    'required' => 'The {field} field is required.',
                    'in_list' => 'Invalid role selected. Please select admin, teacher, or student.'
                ]
            ]
        ]);

        // Run validation
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            return $this->response->setJSON([
                'success' => false,
                'message' => implode('<br>', $errors),
                'errors' => $errors
            ]);
        }

        // Get validated and sanitized form data
        $name = trim($this->request->getPost('name'));
        $email = trim(strtolower($this->request->getPost('email')));
        $role = $this->request->getPost('role');

        // Auto-generate password
        $password = 'BasteLMS123.';
        
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Save user data to database
        $userModel = new \App\Models\UserModel();
        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $result = $userModel->insert($userData);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'User created successfully! Default password: BasteLMS123.',
                    'user' => [
                        'id' => $result,
                        'name' => $name,
                        'email' => $email,
                        'role' => $role
                    ],
                    'password' => $password
                ]);
            } else {
                $errors = $userModel->errors();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create user. ' . json_encode($errors)
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ]);
        }
    }

    public function updateRole($userId)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        // Prevent editing admin role
        if (strtolower($user['role']) === 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Admin role cannot be edited.'
            ]);
        }

        $newRole = $this->request->getPost('role');

        // Validate role
        if (!in_array($newRole, ['teacher', 'student'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid role selected.'
            ]);
        }

        // Update user role
        $updateData = [
            'role' => $newRole,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($userModel->update($userId, $updateData)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User role updated successfully.',
                'newRole' => $newRole
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update user role.'
            ]);
        }
    }

    public function activateUser($userId)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        // Prevent activating/deactivating admin accounts
        if (strtolower($user['role']) === 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Admin accounts cannot be deactivated.'
            ]);
        }

        // Activate user
        $updateData = [
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($userModel->update($userId, $updateData)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User activated successfully.',
                'is_active' => 1
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to activate user.'
            ]);
        }
    }

    public function deactivateUser($userId)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        // Prevent activating/deactivating admin accounts
        if (strtolower($user['role']) === 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Admin accounts cannot be deactivated.'
            ]);
        }

        // Deactivate user (soft delete - doesn't remove from database)
        $updateData = [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($userModel->update($userId, $updateData)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'User deactivated successfully. Account remains in database.',
                'is_active' => 0
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to deactivate user.'
            ]);
        }
    }


    // Course Management
    public function createCourse()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        // Set validation rules
        $validation = \Config\Services::validation();
        $validation->setRules([
            'title' => [
                'label' => 'Course Title',
                'rules' => 'required|min_length[3]|max_length[150]',
                'errors' => [
                    'required' => 'The {field} field is required.',
                    'min_length' => 'The {field} must be at least {param} characters long.',
                    'max_length' => 'The {field} cannot exceed {param} characters.'
                ]
            ],
            'control_number' => [
                'label' => 'Control Number',
                'rules' => 'required|exact_length[4]|is_unique[courses.control_number]',
                'errors' => [
                    'required' => 'The {field} field is required.',
                    'exact_length' => 'The {field} must be exactly {param} characters.',
                    'is_unique' => 'The {field} already exists. Please use a different control number.'
                ]
            ],
            'description' => [
                'label' => 'Description',
                'rules' => 'permit_empty|max_length[1000]',
                'errors' => [
                    'max_length' => 'The {field} cannot exceed {param} characters.'
                ]
            ]
        ]);

        // Run validation
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            return $this->response->setJSON([
                'success' => false,
                'message' => implode('<br>', $errors),
                'errors' => $errors
            ]);
        }

        // Get validated and sanitized form data
        $title = trim($this->request->getPost('title'));
        $controlNumber = trim($this->request->getPost('control_number'));
        $description = trim($this->request->getPost('description'));
        $academicYearId = $this->request->getPost('academic_year_id') ?: null;
        $semesterId = $this->request->getPost('semester_id') ?: null;
        $yearLevelId = $this->request->getPost('year_level_id') ?: null;

        // Validate control number is provided and exactly 4 characters
        if (empty($controlNumber)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Control number is required.'
            ]);
        }

        if (strlen($controlNumber) !== 4) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Control number must be exactly 4 characters.'
            ]);
        }

        // Check if control number is unique
        $courseModel = new \App\Models\CourseModel();
        $existingCourse = $courseModel->where('control_number', $controlNumber)->first();
        if ($existingCourse) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Control number already exists. Please use a different control number. Course names can be the same, but control numbers must be unique.'
            ]);
        }

        // Validate that the selected academic year is active (similar to GALORPOT's active school year check)
        $academicYearModel = new \App\Models\AcademicYearModel();
        $selectedAcademicYear = $academicYearModel->find($academicYearId);
        
        if (!$selectedAcademicYear) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Selected academic year does not exist.'
            ]);
        }
        
        if (!$selectedAcademicYear['is_active']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ERROR: Cannot create course for Academic Year ' . $selectedAcademicYear['year_start'] . '-' . $selectedAcademicYear['year_end'] . '. Only active academic years can be used for course creation. Please select an active academic year.'
            ]);
        }

        // Verify that the semester exists and belongs to the academic year
        $semesterModel = new \App\Models\SemesterModel();
        $semester = $semesterModel->find($semesterId);
        
        if (!$semester) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Selected semester does not exist.'
            ]);
        }
        
        if ($semester['academic_year_id'] != $academicYearId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Selected semester does not belong to the selected academic year.'
            ]);
        }

        // Get units (if provided)
        $units = $this->request->getPost('units') ? (int) $this->request->getPost('units') : 0;
        if ($units < 0 || $units > 5) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Units must be between 0 and 5.'
            ]);
        }

        // Save course data to database
        $courseModel = new \App\Models\CourseModel();
        $courseData = [
            'title' => $title,
            'control_number' => $controlNumber,
            'units' => $units,
            'description' => $description ?: null,
            'academic_year_id' => $academicYearId,
            'semester_id' => $semesterId,
            'year_level_id' => $yearLevelId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $result = $courseModel->insert($courseData);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Course created successfully!',
                    'course' => [
                        'id' => $result,
                        'title' => $title,
                        'description' => $description
                    ],
                    'csrf_token' => csrf_hash()
                ]);
            } else {
                $errors = $courseModel->errors();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to create course. ' . json_encode($errors)
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create course: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCourse()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        // Get course ID from POST data
        $courseId = $this->request->getPost('id');
        
        if (empty($courseId) || !is_numeric($courseId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID provided.'
            ]);
        }

        $courseId = (int) $courseId;

        // Get form data
        $title = trim($this->request->getPost('title') ?? '');
        $controlNumber = trim($this->request->getPost('control_number') ?? '');
        $units = $this->request->getPost('units');
        $description = trim($this->request->getPost('description') ?? '');
        $academicYearId = $this->request->getPost('academic_year_id');
        $semesterId = $this->request->getPost('semester_id');
        $yearLevelId = $this->request->getPost('year_level_id');

        // Validate required fields
        if (empty($title)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course title is required.'
            ]);
        }

        if (empty($controlNumber)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Control number is required.'
            ]);
        }

        if (strlen($controlNumber) !== 4 || !is_numeric($controlNumber)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Control number must be exactly 4 digits.'
            ]);
        }

        if (!is_numeric($units) || $units < 0 || $units > 5) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Units must be a number between 0 and 5.'
            ]);
        }

        // Validate academic year, semester, and year level
        if (empty($academicYearId) || !is_numeric($academicYearId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Academic year is required.'
            ]);
        }

        if (empty($semesterId) || !is_numeric($semesterId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Semester is required.'
            ]);
        }

        if (empty($yearLevelId) || !is_numeric($yearLevelId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Year level is required.'
            ]);
        }

        $courseModel = new \App\Models\CourseModel();

        // Check if course exists
        $course = $courseModel->find($courseId);
        if (!$course) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        // Check if control number is already taken by another course
        $existingCourse = $courseModel->where('control_number', $controlNumber)
                                     ->where('id !=', $courseId)
                                     ->first();
        if ($existingCourse) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Control number is already taken by another course.'
            ]);
        }

        // Prepare update data
        $updateData = [
            'title' => $title,
            'control_number' => $controlNumber,
            'units' => (int) $units,
            'description' => !empty($description) ? $description : null,
            'academic_year_id' => (int) $academicYearId,
            'semester_id' => (int) $semesterId,
            'year_level_id' => (int) $yearLevelId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            if ($courseModel->update($courseId, $updateData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Course updated successfully!',
                    'csrf_token' => csrf_hash()
                ]);
            } else {
                $errors = $courseModel->errors();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update course. ' . (!empty($errors) ? implode(', ', $errors) : 'Unknown error')
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating course: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update course: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteCourse($courseId)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        // Validate course_id
        if (empty($courseId) || !is_numeric($courseId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID provided.'
            ]);
        }

        $courseId = (int) $courseId;

        $courseModel = new \App\Models\CourseModel();
        $enrollmentModel = new \App\Models\EnrollmentModel();
        $materialModel = new \App\Models\MaterialModel();
        $assignmentModel = new \App\Models\AssignmentModel();

        // Check if course exists
        $course = $courseModel->find($courseId);
        if (!$course) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        $courseTitle = $course['title'];

        try {
            // Soft delete the course (like GALORPOT's flow - soft delete, not hard delete)
            // This preserves enrollments, materials, and assignments for potential restoration
            if ($courseModel->delete($courseId)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => "Course '{$courseTitle}' has been marked as deleted successfully. You can restore it later if needed.",
                    'csrf_token' => csrf_hash()
                ]);
            } else {
                $errors = $courseModel->errors();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete course. ' . (!empty($errors) ? implode(', ', $errors) : 'Unknown error')
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete course: ' . $e->getMessage()
            ]);
        }
    }

    // Academic Year Management
    public function createAcademicYear()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard?section=academic-years');
        }

        $academicYearModel = new \App\Models\AcademicYearModel();
        
        $yearStart = $this->request->getPost('year_start');
        $yearEnd = $this->request->getPost('year_end');
        $isActive = $this->request->getPost('is_active') ? 1 : 0;

        // Validate
        if (empty($yearStart) || empty($yearEnd)) {
            session()->setFlashdata('error', 'Year start and end are required.');
            return redirect()->to('/dashboard?section=academic-years');
        }

        // Get the currently active academic year before deactivating
        $previousActiveYear = $academicYearModel->where('is_active', 1)->first();
        
        // If setting as active, deactivate all others
        if ($isActive) {
            $academicYearModel->set('is_active', 0)->where('id !=', 0)->update();
        }

        $data = [
            'year_start' => $yearStart,
            'year_end' => $yearEnd,
            'is_active' => $isActive,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($academicYearModel->insert($data)) {
            // If academic year is set as active, archive previous active year's courses
            if ($isActive && $previousActiveYear) {
                $completionService = new \App\Libraries\CompletionService();
                // Archive courses from the previous active academic year
                $archiveResult = $completionService->archiveAcademicYearCourses($previousActiveYear['id']);
                
                // Notify all students and teachers about new academic year
                $completionService->notifyAcademicYearOpened([
                    'year_start' => $yearStart,
                    'year_end' => $yearEnd
                ]);
            } elseif ($isActive) {
                // No previous active year, just notify about new academic year
                $completionService = new \App\Libraries\CompletionService();
                $completionService->notifyAcademicYearOpened([
                    'year_start' => $yearStart,
                    'year_end' => $yearEnd
                ]);
            }
            
            // Automatically update active semesters and academic years based on dates (Asia/Manila timezone)
            // This will override manual is_active setting if dates don't match
            try {
                $activationService = new \App\Libraries\SemesterActivationService();
                $activationService->updateActiveSemesters();
            } catch (\Exception $e) {
                log_message('error', 'Error updating active semesters after academic year creation: ' . $e->getMessage());
            }
            
            session()->setFlashdata('success', 'Academic year created successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to create academic year.');
        }

        return redirect()->to('/dashboard?section=academic-years');
    }

    public function updateAcademicYear($id)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard?section=academic-years');
        }

        $academicYearModel = new \App\Models\AcademicYearModel();
        $academicYear = $academicYearModel->find($id);

        if (!$academicYear) {
            session()->setFlashdata('error', 'Academic year not found.');
            return redirect()->to('/dashboard?section=academic-years');
        }

        $yearStart = $this->request->getPost('year_start');
        $yearEnd = $this->request->getPost('year_end');
        $isActive = $this->request->getPost('is_active') ? 1 : 0;

        // Get the currently active academic year before deactivating (excluding the one being updated)
        $previousActiveYear = $academicYearModel->where('is_active', 1)
                                                ->where('id !=', $id)
                                                ->first();
        
        // If setting as active, deactivate all others
        $wasActive = $academicYear['is_active'];
        $completionService = new \App\Libraries\CompletionService();
        
        if ($isActive && !$wasActive) {
            // Academic year is being activated for the first time
            $academicYearModel->set('is_active', 0)->where('id !=', $id)->update();
            
            // Archive courses from the previous active academic year
            if ($previousActiveYear) {
                $completionService->archiveAcademicYearCourses($previousActiveYear['id']);
            }
            
            // Reactivate courses for this academic year
            $completionService->reactivateAcademicYearCourses($id);
            
            // Notify all students and teachers
            $completionService->notifyAcademicYearOpened([
                'year_start' => $yearStart,
                'year_end' => $yearEnd
            ]);
        } elseif ($isActive) {
            // Just deactivate others, no notification needed (already active)
            $academicYearModel->set('is_active', 0)->where('id !=', $id)->update();
        } elseif (!$isActive && $wasActive) {
            // Academic year is being deactivated - archive its courses
            $completionService->archiveAcademicYearCourses($id);
        }

        $data = [
            'year_start' => $yearStart,
            'year_end' => $yearEnd,
            'is_active' => $isActive,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($academicYearModel->update($id, $data)) {
            // Automatically update active semesters and academic years based on dates (Asia/Manila timezone)
            // This will override manual is_active setting if dates don't match
            try {
                $activationService = new \App\Libraries\SemesterActivationService();
                $activationService->updateActiveSemesters();
            } catch (\Exception $e) {
                log_message('error', 'Error updating active semesters after academic year update: ' . $e->getMessage());
            }
            
            session()->setFlashdata('success', 'Academic year updated successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to update academic year.');
        }

        return redirect()->to('/dashboard?section=academic-years');
    }

    public function deleteAcademicYear($id)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard?section=academic-years');
        }

        $academicYearModel = new \App\Models\AcademicYearModel();
        
        // Check if there are semesters or courses using this academic year
        $semesterModel = new \App\Models\SemesterModel();
        $courseModel = new \App\Models\CourseModel();
        
        $semestersCount = $semesterModel->where('academic_year_id', $id)->countAllResults();
        $coursesCount = $courseModel->where('academic_year_id', $id)->countAllResults();

        if ($semestersCount > 0 || $coursesCount > 0) {
            session()->setFlashdata('error', 'Cannot delete academic year. It is being used by semesters or courses.');
            return redirect()->to('/dashboard?section=academic-years');
        }

        if ($academicYearModel->delete($id)) {
            session()->setFlashdata('success', 'Academic year deleted successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to delete academic year.');
        }

        return redirect()->to('/dashboard?section=academic-years');
    }

    // Semester Management
    public function createSemester()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard?section=semesters');
        }

        $semesterModel = new \App\Models\SemesterModel();
        
        $name = $this->request->getPost('name');
        $semester = $this->request->getPost('semester');
        $term = $this->request->getPost('term');
        $academicYearId = $this->request->getPost('academic_year_id');
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');
        $isActive = $this->request->getPost('is_active') ? 1 : 0;

        // Validate
        if (empty($name) || empty($semester) || empty($term) || empty($academicYearId)) {
            session()->setFlashdata('error', 'Name, semester, term, and academic year are required.');
            return redirect()->to('/dashboard?section=semesters');
        }

        // Get the currently active semester in the same academic year before deactivating
        $previousActiveSemester = $semesterModel->where('academic_year_id', $academicYearId)
                                                ->where('is_active', 1)
                                                ->first();
        
        // If setting as active, deactivate all others in the same academic year
        if ($isActive) {
            $semesterModel->where('academic_year_id', $academicYearId)->set('is_active', 0)->update();
        }

        $data = [
            'name' => $name,
            'semester' => $semester,
            'term' => $term,
            'academic_year_id' => $academicYearId,
            'start_date' => $startDate ?: null,
            'end_date' => $endDate ?: null,
            'is_active' => $isActive,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($semesterModel->insert($data)) {
            // Automatically update active semesters based on dates (Asia/Manila timezone)
            // This will override manual is_active setting if dates don't match
            try {
                $activationService = new \App\Libraries\SemesterActivationService();
                $activationService->updateActiveSemesters();
            } catch (\Exception $e) {
                log_message('error', 'Error updating active semesters after creation: ' . $e->getMessage());
            }
            
            // If semester is set as active, archive previous active semester's courses
            if ($isActive && $previousActiveSemester) {
                $completionService = new \App\Libraries\CompletionService();
                $completionService->archiveSemesterCourses($previousActiveSemester['id']);
            }
            
            session()->setFlashdata('success', 'Semester created successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to create semester.');
        }

        return redirect()->to('/dashboard?section=semesters');
    }

    public function updateSemester($id)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard?section=semesters');
        }

        $semesterModel = new \App\Models\SemesterModel();
        $semesterRecord = $semesterModel->find($id);

        if (!$semesterRecord) {
            session()->setFlashdata('error', 'Semester not found.');
            return redirect()->to('/dashboard?section=semesters');
        }

        $name = $this->request->getPost('name');
        $semester = $this->request->getPost('semester');
        $term = $this->request->getPost('term');
        $academicYearId = $this->request->getPost('academic_year_id');
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');
        $isActive = $this->request->getPost('is_active') ? 1 : 0;

        // Get the currently active semester in the same academic year before deactivating (excluding the one being updated)
        $previousActiveSemester = $semesterModel->where('academic_year_id', $academicYearId)
                                                ->where('is_active', 1)
                                                ->where('id !=', $id)
                                                ->first();
        
        $wasActive = $semesterRecord['is_active'];
        
        // If setting as active, deactivate all others in the same academic year
        if ($isActive) {
            $semesterModel->set('is_active', 0)->where('academic_year_id', $academicYearId)->where('id !=', $id)->update();
        }

        $data = [
            'name' => $name,
            'semester' => $semester,
            'term' => $term,
            'academic_year_id' => $academicYearId,
            'start_date' => $startDate ?: null,
            'end_date' => $endDate ?: null,
            'is_active' => $isActive,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($semesterModel->update($id, $data)) {
            // Automatically update active semesters based on dates (Asia/Manila timezone)
            // This will override manual is_active setting if dates don't match
            try {
                $activationService = new \App\Libraries\SemesterActivationService();
                $activationService->updateActiveSemesters();
            } catch (\Exception $e) {
                log_message('error', 'Error updating active semesters after update: ' . $e->getMessage());
            }
            
            $completionService = new \App\Libraries\CompletionService();
            
            if ($isActive && !$wasActive) {
                // Semester is being activated/reactivated
                // Archive courses from the previous active semester
                if ($previousActiveSemester) {
                    $completionService->archiveSemesterCourses($previousActiveSemester['id']);
                }
                
                // Reactivate courses for this semester
                $completionService->reactivateSemesterCourses($id);
            } elseif ($isActive) {
                // Semester is already active, just deactivate others (no action needed)
            } elseif (!$isActive && $wasActive) {
                // Semester is being deactivated - archive its courses
                $completionService->archiveSemesterCourses($id);
            }
            
            session()->setFlashdata('success', 'Semester updated successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to update semester.');
        }

        return redirect()->to('/dashboard?section=semesters');
    }

    public function deleteSemester($id)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard?section=semesters');
        }

        $semesterModel = new \App\Models\SemesterModel();
        $courseModel = new \App\Models\CourseModel();
        
        // Check if there are courses using this semester
        $coursesCount = $courseModel->where('semester_id', $id)->countAllResults();

        if ($coursesCount > 0) {
            session()->setFlashdata('error', 'Cannot delete semester. It is being used by courses.');
            return redirect()->to('/dashboard?section=semesters');
        }

        if ($semesterModel->delete($id)) {
            session()->setFlashdata('success', 'Semester deleted successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to delete semester.');
        }

        return redirect()->to('/dashboard?section=semesters');
    }

    // Year Level Management
    public function createYearLevel()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard?section=year-levels');
        }

        $yearLevelModel = new \App\Models\YearLevelModel();
        
        $name = $this->request->getPost('name');
        $level = $this->request->getPost('level');
        $description = $this->request->getPost('description');

        // Validate
        if (empty($name) || empty($level)) {
            session()->setFlashdata('error', 'Name and level are required.');
            return redirect()->to('/dashboard?section=year-levels');
        }

        $data = [
            'name' => $name,
            'level' => $level,
            'description' => $description ?: null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($yearLevelModel->insert($data)) {
            session()->setFlashdata('success', 'Year level created successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to create year level.');
        }

        return redirect()->to('/dashboard?section=year-levels');
    }

    public function updateYearLevel($id)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard?section=year-levels');
        }

        $yearLevelModel = new \App\Models\YearLevelModel();
        $yearLevel = $yearLevelModel->find($id);

        if (!$yearLevel) {
            session()->setFlashdata('error', 'Year level not found.');
            return redirect()->to('/dashboard?section=year-levels');
        }

        $name = $this->request->getPost('name');
        $level = $this->request->getPost('level');
        $description = $this->request->getPost('description');

        $data = [
            'name' => $name,
            'level' => $level,
            'description' => $description ?: null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($yearLevelModel->update($id, $data)) {
            session()->setFlashdata('success', 'Year level updated successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to update year level.');
        }

        return redirect()->to('/dashboard?section=year-levels');
    }

    public function deleteYearLevel($id)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard?section=year-levels');
        }

        $yearLevelModel = new \App\Models\YearLevelModel();
        $userModel = new \App\Models\UserModel();
        $courseModel = new \App\Models\CourseModel();
        
        // Check if there are users or courses using this year level
        $usersCount = $userModel->where('year_level_id', $id)->countAllResults();
        $coursesCount = $courseModel->where('year_level_id', $id)->countAllResults();

        if ($usersCount > 0 || $coursesCount > 0) {
            session()->setFlashdata('error', 'Cannot delete year level. It is being used by users or courses.');
            return redirect()->to('/dashboard?section=year-levels');
        }

        if ($yearLevelModel->delete($id)) {
            session()->setFlashdata('success', 'Year level deleted successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to delete year level.');
        }

        return redirect()->to('/dashboard?section=year-levels');
    }

    // Assign Year Level to Student
    public function assignYearLevel()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            session()->setFlashdata('error', 'Access denied. Admin role required.');
            return redirect()->to('/dashboard?section=assign-year-level');
        }

        $userModel = new \App\Models\UserModel();
        
        $userId = $this->request->getPost('user_id');
        $yearLevelId = $this->request->getPost('year_level_id');

        // Validate
        if (empty($userId) || empty($yearLevelId)) {
            session()->setFlashdata('error', 'User and year level are required.');
            return redirect()->to('/dashboard?section=assign-year-level');
        }

        $user = $userModel->find($userId);
        if (!$user || strtolower($user['role']) !== 'student') {
            session()->setFlashdata('error', 'User must be a student.');
            return redirect()->to('/dashboard?section=assign-year-level');
        }

        $data = [
            'year_level_id' => $yearLevelId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($userModel->update($userId, $data)) {
            session()->setFlashdata('success', 'Year level assigned successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to assign year level.');
        }

        return redirect()->to('/dashboard?section=assign-year-level');
    }

    // Course Scheduling and Teacher Assignment
    public function addCourseSchedule($courseId)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        // Validate course_id
        if (empty($courseId) || !is_numeric($courseId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID provided.'
            ]);
        }

        $courseId = (int) $courseId;

        // Validate course exists
        $courseModel = new \App\Models\CourseModel();
        $course = $courseModel->find($courseId);
        if (!$course) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        // Validate input
        $validation = \Config\Services::validation();
        $validation->setRules([
            'day_of_week' => [
                'label' => 'Day of Week',
                'rules' => 'required|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
            ],
            'start_time' => [
                'label' => 'Start Time',
                'rules' => 'required',
            ],
            'end_time' => [
                'label' => 'End Time',
                'rules' => 'required',
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            return $this->response->setJSON([
                'success' => false,
                'message' => implode('<br>', $errors),
                'errors' => $errors
            ]);
        }

        $scheduleModel = new \App\Models\CourseScheduleModel();
        
        $data = [
            'course_id' => $courseId,
            'day_of_week' => $this->request->getPost('day_of_week'),
            'start_time' => $this->request->getPost('start_time'),
            'end_time' => $this->request->getPost('end_time'),
            'room' => null, // Room field removed
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $result = $scheduleModel->insert($data);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Course schedule added successfully!',
                    'schedule_id' => $result,
                    'csrf_token' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to add schedule.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to add schedule: ' . $e->getMessage()
            ]);
        }
    }

    public function deleteCourseSchedule($courseId, $scheduleId)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        $scheduleModel = new \App\Models\CourseScheduleModel();
        $schedule = $scheduleModel->find($scheduleId);

        if (!$schedule || $schedule['course_id'] != $courseId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Schedule not found.'
            ]);
        }

        if ($scheduleModel->delete($scheduleId)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Schedule deleted successfully.',
                'csrf_token' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete schedule.'
            ]);
        }
    }

    public function assignTeacherToCourse($courseId)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        // Validate course_id
        if (empty($courseId) || !is_numeric($courseId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID provided.'
            ]);
        }

        $courseId = (int) $courseId;

        // Validate course exists
        $courseModel = new \App\Models\CourseModel();
        $course = $courseModel->find($courseId);
        if (!$course) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        $teacherId = $this->request->getPost('teacher_id');
        $isPrimary = $this->request->getPost('is_primary') ? 1 : 0;

        // Validate teacher_id
        if (empty($teacherId) || !is_numeric($teacherId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid teacher ID provided.'
            ]);
        }

        $teacherId = (int) $teacherId;

        // Validate teacher exists and is a teacher
        $userModel = new \App\Models\UserModel();
        $teacher = $userModel->find($teacherId);
        if (!$teacher || strtolower($teacher['role']) !== 'teacher') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Teacher not found or user is not a teacher.'
            ]);
        }

        $courseTeacherModel = new \App\Models\CourseTeacherModel();

        // Check if teacher is already assigned to this course
        if ($courseTeacherModel->isTeacherAssigned($courseId, $teacherId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Teacher is already assigned to this course.'
            ]);
        }

        // Check for schedule conflicts
        $conflictCheck = $this->checkTeacherScheduleConflict($courseId, $teacherId);
        if (!$conflictCheck['allowed']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $conflictCheck['message']
            ]);
        }

        try {
            $result = $courseTeacherModel->assignTeacher($courseId, $teacherId, $isPrimary);
            
            if ($result) {
                // Send notification to the assigned teacher
                $notificationModel = new \App\Models\NotificationModel();
                $roleText = $isPrimary ? 'primary teacher' : 'teacher';
                $notificationMessage = "You have been assigned as {$roleText} to the course '{$course['title']}'.";
                $notificationModel->insert([
                    'user_id' => $teacherId,
                    'message' => $notificationMessage,
                    'type' => 'assignment',
                    'is_read' => 0
                ]);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Teacher assigned to course successfully!',
                    'csrf_token' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to assign teacher.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to assign teacher: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check for schedule conflicts when assigning a teacher to a course
     * Returns array with 'allowed' (bool) and 'message' (string)
     */
    private function checkTeacherScheduleConflict($newCourseId, $teacherId)
    {
        $courseModel = new \App\Models\CourseModel();
        $courseTeacherModel = new \App\Models\CourseTeacherModel();
        $scheduleModel = new \App\Models\CourseScheduleModel();
        $semesterModel = new \App\Models\SemesterModel();

        // Get the new course details
        $newCourse = $courseModel->find($newCourseId);
        if (!$newCourse) {
            return ['allowed' => false, 'message' => 'Course not found.'];
        }

        // Get all courses the teacher is already assigned to
        $existingAssignments = $courseTeacherModel->getCoursesByTeacher($teacherId);
        
        if (empty($existingAssignments)) {
            // No existing assignments, no conflict possible
            return ['allowed' => true, 'message' => ''];
        }

        // Get schedules for the new course
        $newCourseSchedules = $scheduleModel->getSchedulesByCourse($newCourseId);
        
        if (empty($newCourseSchedules)) {
            // New course has no schedule, no conflict possible
            return ['allowed' => true, 'message' => ''];
        }

        // Get semester info for the new course
        $newCourseSemester = null;
        if ($newCourse['semester_id']) {
            $newCourseSemester = $semesterModel->find($newCourse['semester_id']);
        }

        // Check each existing assignment for conflicts
        foreach ($existingAssignments as $existingAssignment) {
            $existingCourseId = $existingAssignment['course_id'];
            
            // Skip if it's the same course (shouldn't happen, but just in case)
            if ($existingCourseId == $newCourseId) {
                continue;
            }

            // Get the existing course details
            $existingCourse = $courseModel->find($existingCourseId);
            if (!$existingCourse) {
                continue;
            }

            // Check if courses are in the same academic year, semester, and term (similar to GALORPOT's logic)
            // For conflict, courses must be in the same academic year, semester, and term
            $sameAcademicPeriod = false;
            
            // Check academic year
            if ($newCourse['academic_year_id'] && $existingCourse['academic_year_id']) {
                if ($newCourse['academic_year_id'] != $existingCourse['academic_year_id']) {
                    // Different academic years, no conflict
                    continue;
                }
            } else {
                // If either course doesn't have academic year, skip conflict check
                continue;
            }
            
            // Check semester (must match)
            if ($newCourse['semester_id'] && $existingCourse['semester_id']) {
                if ($newCourse['semester_id'] != $existingCourse['semester_id']) {
                    // Different semesters, no conflict
                    continue;
                }
            } else {
                // If either course doesn't have semester, skip conflict check
                continue;
            }
            
            // Get semester details to check term
            $newCourseSemester = $semesterModel->find($newCourse['semester_id']);
            $existingCourseSemester = $semesterModel->find($existingCourse['semester_id']);
            
            // Check term (must match for conflict)
            if ($newCourseSemester && $existingCourseSemester) {
                if (isset($newCourseSemester['term']) && isset($existingCourseSemester['term'])) {
                    if ($newCourseSemester['term'] != $existingCourseSemester['term']) {
                        // Different terms, no conflict
                        continue;
                    }
                }
            }
            
            // If we reach here, courses are in the same academic year, semester, and term - check for schedule conflicts
            $sameAcademicPeriod = true;

            // Get schedules for the existing course
            $existingCourseSchedules = $scheduleModel->getSchedulesByCourse($existingCourseId);
            
            if (empty($existingCourseSchedules)) {
                // Existing course has no schedule, no conflict
                continue;
            }

            // Check for overlapping schedules
            foreach ($newCourseSchedules as $newSchedule) {
                foreach ($existingCourseSchedules as $existingSchedule) {
                    // Check if same day
                    if ($newSchedule['day_of_week'] !== $existingSchedule['day_of_week']) {
                        continue;
                    }

                    // Check if times overlap
                    $newStart = strtotime($newSchedule['start_time']);
                    $newEnd = strtotime($newSchedule['end_time']);
                    $existingStart = strtotime($existingSchedule['start_time']);
                    $existingEnd = strtotime($existingSchedule['end_time']);

                    // Check for time overlap
                    if (($newStart < $existingEnd && $newEnd > $existingStart)) {
                        // Conflict found!
                        $existingCourseTitle = $existingCourse['title'] ?? 'Unknown Course';
                        $day = $newSchedule['day_of_week'];
                        $newTime = date('g:i A', $newStart) . ' - ' . date('g:i A', $newEnd);
                        $existingTime = date('g:i A', $existingStart) . ' - ' . date('g:i A', $existingEnd);
                        
                        $semesterInfo = '';
                        if ($newCourseSemester) {
                            $semesterName = $newCourseSemester['name'] ?? '';
                            $semester = $newCourseSemester['semester'] ?? '';
                            $term = $newCourseSemester['term'] ?? '';
                            $semesterInfo = " ({$semesterName} - {$semester} Semester, {$term} Term)";
                        }
                        
                        // Get academic year info
                        $academicYearModel = new \App\Models\AcademicYearModel();
                        $academicYear = $academicYearModel->find($newCourse['academic_year_id']);
                        $academicYearInfo = '';
                        if ($academicYear) {
                            $academicYearInfo = " - Academic Year {$academicYear['year_start']}-{$academicYear['year_end']}";
                        }

                        return [
                            'allowed' => false,
                            'message' => "Schedule conflict detected! The teacher is already assigned to course '{$existingCourseTitle}' with a schedule on {$day} from {$existingTime}{$semesterInfo}{$academicYearInfo}. The new course has a conflicting schedule on {$day} from {$newTime}{$semesterInfo}{$academicYearInfo}. Please choose a different time or assign a different teacher."
                        ];
                    }
                }
            }
        }

        // No conflicts found
        return ['allowed' => true, 'message' => ''];
    }

    // Update Teacher Assignment
    public function updateTeacherAssignment($courseId)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        // Validate course_id
        if (empty($courseId) || !is_numeric($courseId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID provided.'
            ]);
        }

        $courseId = (int) $courseId;
        $assignmentId = $this->request->getPost('assignment_id');
        $teacherId = $this->request->getPost('teacher_id');
        $isPrimary = $this->request->getPost('is_primary') ? 1 : 0;

        // Validate assignment_id (this is the course_teachers.id)
        if (empty($assignmentId) || !is_numeric($assignmentId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid assignment ID provided.'
            ]);
        }

        $assignmentId = (int) $assignmentId;

        // Validate teacher_id
        if (empty($teacherId) || !is_numeric($teacherId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid teacher ID provided.'
            ]);
        }

        $teacherId = (int) $teacherId;

        $courseTeacherModel = new \App\Models\CourseTeacherModel();
        $courseModel = new \App\Models\CourseModel();

        // Get existing assignment
        $existingAssignment = $courseTeacherModel->find($assignmentId);
        if (!$existingAssignment || $existingAssignment['course_id'] != $courseId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Assignment not found or does not belong to this course.'
            ]);
        }

        // Verify course exists
        $course = $courseModel->find($courseId);
        if (!$course) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        // Validate teacher exists and is a teacher
        $userModel = new \App\Models\UserModel();
        $teacher = $userModel->find($teacherId);
        if (!$teacher || strtolower($teacher['role']) !== 'teacher') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Teacher not found or user is not a teacher.'
            ]);
        }

        // Check if teacher is already assigned to this course (excluding current assignment)
        if ($courseTeacherModel->isTeacherAssigned($courseId, $teacherId)) {
            // Check if it's a different assignment (different teacher_id in existing assignment)
            if ($existingAssignment['teacher_id'] != $teacherId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Teacher is already assigned to this course.'
                ]);
            }
        }

        // Check for schedule conflicts if teacher changed
        if ($existingAssignment['teacher_id'] != $teacherId) {
            $conflictCheck = $this->checkTeacherScheduleConflict($courseId, $teacherId);
            if (!$conflictCheck['allowed']) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $conflictCheck['message']
                ]);
            }
        }

        try {
            // Update the assignment
            $updateData = [
                'teacher_id' => $teacherId,
                'is_primary' => $isPrimary
            ];

            // If setting as primary, unset other primary teachers for this course
            if ($isPrimary) {
                $courseTeacherModel->where('course_id', $courseId)
                                 ->where('id !=', $assignmentId)
                                 ->set('is_primary', 0)
                                 ->update();
            }

            if ($courseTeacherModel->update($assignmentId, $updateData)) {
                // Send notification if teacher was changed
                $notificationModel = new \App\Models\NotificationModel();
                if ($existingAssignment['teacher_id'] != $teacherId) {
                    // New teacher assigned
                    $roleText = $isPrimary ? 'primary teacher' : 'teacher';
                    $notificationMessage = "You have been assigned as {$roleText} to the course '{$course['title']}'.";
                    $notificationModel->insert([
                        'user_id' => $teacherId,
                        'message' => $notificationMessage,
                        'type' => 'assignment',
                        'is_read' => 0
                    ]);
                } elseif ($existingAssignment['is_primary'] != $isPrimary) {
                    // Primary status changed for same teacher
                    $roleText = $isPrimary ? 'primary teacher' : 'teacher';
                    $notificationMessage = "Your role for the course '{$course['title']}' has been updated. You are now assigned as {$roleText}.";
                    $notificationModel->insert([
                        'user_id' => $teacherId,
                        'message' => $notificationMessage,
                        'type' => 'assignment',
                        'is_read' => 0
                    ]);
                }
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Teacher assignment updated successfully!',
                    'csrf_token' => csrf_hash()
                ]);
            } else {
                $errors = $courseTeacherModel->errors();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update teacher assignment. ' . (!empty($errors) ? implode(', ', $errors) : '')
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update teacher assignment: ' . $e->getMessage()
            ]);
        }
    }

    public function removeTeacherFromCourse($courseId, $teacherId)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        // Validate IDs
        if (empty($courseId) || !is_numeric($courseId) || empty($teacherId) || !is_numeric($teacherId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course or teacher ID provided.'
            ]);
        }

        $courseId = (int) $courseId;
        $teacherId = (int) $teacherId;

        $courseTeacherModel = new \App\Models\CourseTeacherModel();

        if ($courseTeacherModel->removeTeacher($courseId, $teacherId)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Teacher removed from course successfully.',
                'csrf_token' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to remove teacher or assignment not found.'
            ]);
        }
    }

    // Get course schedules
    public function getCourseSchedules($courseId)
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied.'
            ]);
        }

        $scheduleModel = new \App\Models\CourseScheduleModel();
        $schedules = $scheduleModel->getSchedulesByCourse($courseId);

        return $this->response->setJSON([
            'success' => true,
            'schedules' => $schedules
        ]);
    }

    // Get course teachers
    public function getCourseTeachers($courseId)
    {
        // Set JSON response header
        $this->response->setContentType('application/json');
        
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied.'
            ])->setStatusCode(403);
        }

        try {
            $courseTeacherModel = new \App\Models\CourseTeacherModel();
            
            // Get active teachers for the course
            $teachers = [];
            try {
                $teachers = $courseTeacherModel->getTeachersByCourse($courseId);
            } catch (\Exception $e) {
                log_message('error', 'Error getting teachers by course: ' . $e->getMessage());
                // Continue with empty array if there's an error
                $teachers = [];
            }
            
            // Get deleted teacher assignments (like GALORPOT's flow)
            $deletedTeachers = [];
            try {
                $deletedTeachers = $courseTeacherModel->withDeleted()
                                                      ->onlyDeleted()
                                                      ->select('course_teachers.*, users.name as teacher_name, users.email as teacher_email')
                                                      ->join('users', 'users.id = course_teachers.teacher_id')
                                                      ->where('course_teachers.course_id', $courseId)
                                                      ->orderBy('course_teachers.deleted_at', 'DESC')
                                                      ->findAll();
            } catch (\Exception $e) {
                log_message('error', 'Error getting deleted teachers: ' . $e->getMessage());
                // Continue with empty array if there's an error
                $deletedTeachers = [];
            }

            return $this->response->setJSON([
                'success' => true,
                'teachers' => $teachers ?: [],
                'deleted_teachers' => $deletedTeachers ?: [],
                'csrf_token' => csrf_hash()
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in getCourseTeachers: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error loading teachers: ' . $e->getMessage(),
                'teachers' => [],
                'deleted_teachers' => []
            ])->setStatusCode(500);
        }
    }

    // Restore Course (like GALORPOT's flow)
    public function restoreCourse()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        $courseId = $this->request->getPost('id');
        
        if (empty($courseId) || !is_numeric($courseId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID provided.'
            ]);
        }

        $courseId = (int) $courseId;

        $courseModel = new \App\Models\CourseModel();
        
        // Get deleted course
        $course = $courseModel->withDeleted()->find($courseId);
        
        if (!$course) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }
        
        if (empty($course['deleted_at'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course is not deleted.'
            ]);
        }

        // Restore the course
        if ($courseModel->update($courseId, ['deleted_at' => null])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => "Course '{$course['title']}' restored successfully!",
                'csrf_token' => csrf_hash()
            ]);
        } else {
            $errors = $courseModel->errors();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to restore course. ' . (!empty($errors) ? implode(', ', $errors) : 'Unknown error')
            ]);
        }
    }

    // Reactivate Completed Course
    public function reactivateCourse()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        $courseId = $this->request->getPost('id');
        
        if (empty($courseId) || !is_numeric($courseId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID provided.'
            ]);
        }

        $courseId = (int) $courseId;

        $courseModel = new \App\Models\CourseModel();
        
        // Get completed course
        $course = $courseModel->find($courseId);
        
        if (!$course) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }
        
        if ($course['status'] !== 'completed') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course is not completed. Only completed courses can be reactivated.'
            ]);
        }

        // Reactivate the course (set status to null/active and clear completed_at)
        if ($courseModel->update($courseId, [
            'status' => null,
            'completed_at' => null
        ])) {
            // Also reactivate related enrollments if needed
            $enrollmentModel = new \App\Models\EnrollmentModel();
            $enrollments = $enrollmentModel->where('course_id', $courseId)
                                         ->where('status', 'completed')
                                         ->findAll();
            
            foreach ($enrollments as $enrollment) {
                // Reactivate enrollments back to approved status
                $enrollmentModel->update($enrollment['id'], [
                    'status' => 'approved',
                    'completed_at' => null
                ]);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'message' => "Course '{$course['title']}' has been reactivated successfully!",
                'csrf_token' => csrf_hash()
            ]);
        } else {
            $errors = $courseModel->errors();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to reactivate course. ' . (!empty($errors) ? implode(', ', $errors) : 'Unknown error')
            ]);
        }
    }

    // Restore Teacher Assignment (like GALORPOT's flow)
    public function restoreTeacherAssignment()
    {
        if (!session()->get('logged_in') || strtolower(session('role')) !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ]);
        }

        $assignmentId = $this->request->getPost('id');
        
        if (empty($assignmentId) || !is_numeric($assignmentId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid assignment ID provided.'
            ]);
        }

        $assignmentId = (int) $assignmentId;

        $courseTeacherModel = new \App\Models\CourseTeacherModel();
        
        // Get deleted assignment
        $assignment = $courseTeacherModel->withDeleted()->find($assignmentId);
        
        if (!$assignment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Teacher assignment not found.'
            ]);
        }
        
        if (empty($assignment['deleted_at'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Teacher assignment is not deleted.'
            ]);
        }

        // Restore the assignment
        if ($courseTeacherModel->update($assignmentId, ['deleted_at' => null])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Teacher assignment restored successfully!',
                'csrf_token' => csrf_hash()
            ]);
        } else {
            $errors = $courseTeacherModel->errors();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to restore teacher assignment. ' . (!empty($errors) ? implode(', ', $errors) : 'Unknown error')
            ]);
        }
    }
}
