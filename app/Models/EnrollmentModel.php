<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table            = 'enrollments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'course_id', 'enrollment_date', 'status', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason', 'completed_at', 'created_at', 'updated_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'user_id' => 'required|integer',
        'course_id' => 'required|integer'
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Enroll a user in a course
     */
    public function enrollUser($data)
    {
        // Set enrollment_date to current datetime if not provided
        // This matches the migration field requirement
        if (!isset($data['enrollment_date'])) {
            $data['enrollment_date'] = date('Y-m-d H:i:s');
        }
        
        return $this->insert($data);
    }

    /**
     * Get all courses a user is enrolled in (all statuses)
     */
    public function getUserEnrollments($user_id)
    {
        return $this->select('enrollments.*, courses.title, courses.description')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->where('enrollments.user_id', $user_id)
                    ->orderBy('enrollments.created_at', 'DESC')
                    ->findAll();
    }
    
    /**
     * Get approved enrollments for a user
     */
    public function getApprovedEnrollments($user_id)
    {
        return $this->select('enrollments.*, courses.title, courses.description')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->where('enrollments.user_id', $user_id)
                    ->where('enrollments.status', 'approved')
                    ->orderBy('enrollments.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Check if a user is already enrolled in a specific course (any status)
     */
    public function isAlreadyEnrolled($user_id, $course_id)
    {
        $enrollment = $this->where('user_id', $user_id)
                          ->where('course_id', $course_id)
                          ->first();
        
        return $enrollment !== null;
    }
    
    /**
     * Check if a user is approved and enrolled in a specific course
     */
    public function isApprovedEnrolled($user_id, $course_id)
    {
        $enrollment = $this->where('user_id', $user_id)
                          ->where('course_id', $course_id)
                          ->where('status', 'approved')
                          ->first();
        
        return $enrollment !== null;
    }
    
    /**
     * Get pending enrollments for a course
     */
    public function getPendingEnrollments($course_id)
    {
        try {
            // Check if status column exists (for backward compatibility)
            $db = \Config\Database::connect();
            $fields = $db->getFieldNames('enrollments');
            $hasStatusColumn = in_array('status', $fields);
            
            if ($hasStatusColumn) {
                return $this->select('enrollments.*, users.name as student_name, users.email as student_email, users.year_level_id')
                            ->join('users', 'users.id = enrollments.user_id')
                            ->where('enrollments.course_id', $course_id)
                            ->where('enrollments.status', 'pending')
                            ->orderBy('enrollments.created_at', 'DESC')
                            ->findAll();
            } else {
                // If status column doesn't exist, return empty array (migration not run yet)
                log_message('info', 'Status column not found in enrollments table. Migration may need to be run.');
                return [];
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in getPendingEnrollments: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get pending enrollments for courses assigned to a teacher
     */
    public function getPendingEnrollmentsForTeacher($teacher_id)
    {
        try {
            // Check if status column exists (for backward compatibility)
            $db = \Config\Database::connect();
            $fields = $db->getFieldNames('enrollments');
            $hasStatusColumn = in_array('status', $fields);
            
            if ($hasStatusColumn) {
                return $this->select('enrollments.*, users.name as student_name, users.email as student_email, courses.title as course_title, courses.id as course_id')
                            ->join('users', 'users.id = enrollments.user_id')
                            ->join('courses', 'courses.id = enrollments.course_id')
                            ->join('course_teachers', 'course_teachers.course_id = courses.id')
                            ->where('course_teachers.teacher_id', $teacher_id)
                            ->where('enrollments.status', 'pending')
                            ->groupBy('enrollments.id')
                            ->orderBy('enrollments.created_at', 'DESC')
                            ->findAll();
            } else {
                // If status column doesn't exist, return empty array (migration not run yet)
                log_message('info', 'Status column not found in enrollments table. Migration may need to be run.');
                return [];
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in getPendingEnrollmentsForTeacher: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Approve an enrollment
     */
    public function approveEnrollment($enrollment_id, $approved_by)
    {
        return $this->update($enrollment_id, [
            'status' => 'approved',
            'approved_by' => $approved_by,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Reject an enrollment
     */
    public function rejectEnrollment($enrollment_id, $rejected_by, $rejection_reason = null)
    {
        return $this->update($enrollment_id, [
            'status' => 'rejected',
            'rejected_by' => $rejected_by,
            'rejected_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $rejection_reason
        ]);
    }

    /**
     * Get enrollment count for a course
     */
    public function getCourseEnrollmentCount($course_id)
    {
        return $this->where('course_id', $course_id)->countAllResults();
    }

    /**
     * Get all enrollments with user and course details
     */
    public function getEnrollmentsWithDetails()
    {
        return $this->select('enrollments.*, users.name as user_name, users.email, courses.title as course_title')
                    ->join('users', 'users.id = enrollments.user_id')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->orderBy('enrollments.created_at', 'DESC')
                    ->findAll();
    }
    
    /**
     * Unenroll a student from a course (delete enrollment)
     */
    public function unenrollStudent($enrollment_id)
    {
        return $this->delete($enrollment_id);
    }
    
    /**
     * Get enrollment by ID with course and user details
     */
    public function getEnrollmentWithDetails($enrollment_id)
    {
        return $this->select('enrollments.*, users.name as student_name, users.email as student_email, courses.title as course_title, courses.id as course_id')
                    ->join('users', 'users.id = enrollments.user_id')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->where('enrollments.id', $enrollment_id)
                    ->first();
    }
}
