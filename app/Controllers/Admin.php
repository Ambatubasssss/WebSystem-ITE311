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

        // If setting as active, deactivate all others
        if ($isActive) {
            $academicYearModel->set('is_active', 0)->where('id !=', $id)->update();
        }

        $data = [
            'year_start' => $yearStart,
            'year_end' => $yearEnd,
            'is_active' => $isActive,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($academicYearModel->update($id, $data)) {
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
        $term = $this->request->getPost('term');
        $academicYearId = $this->request->getPost('academic_year_id');
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');
        $isActive = $this->request->getPost('is_active') ? 1 : 0;

        // Validate
        if (empty($name) || empty($term) || empty($academicYearId)) {
            session()->setFlashdata('error', 'Name, term, and academic year are required.');
            return redirect()->to('/dashboard?section=semesters');
        }

        // If setting as active, deactivate all others in the same academic year
        if ($isActive) {
            $semesterModel->where('academic_year_id', $academicYearId)->set('is_active', 0)->update();
        }

        $data = [
            'name' => $name,
            'term' => $term,
            'academic_year_id' => $academicYearId,
            'start_date' => $startDate ?: null,
            'end_date' => $endDate ?: null,
            'is_active' => $isActive,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($semesterModel->insert($data)) {
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
        $semester = $semesterModel->find($id);

        if (!$semester) {
            session()->setFlashdata('error', 'Semester not found.');
            return redirect()->to('/dashboard?section=semesters');
        }

        $name = $this->request->getPost('name');
        $term = $this->request->getPost('term');
        $academicYearId = $this->request->getPost('academic_year_id');
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');
        $isActive = $this->request->getPost('is_active') ? 1 : 0;

        // If setting as active, deactivate all others in the same academic year
        if ($isActive) {
            $semesterModel->set('is_active', 0)->where('academic_year_id', $academicYearId)->where('id !=', $id)->update();
        }

        $data = [
            'name' => $name,
            'term' => $term,
            'academic_year_id' => $academicYearId,
            'start_date' => $startDate ?: null,
            'end_date' => $endDate ?: null,
            'is_active' => $isActive,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($semesterModel->update($id, $data)) {
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
}
