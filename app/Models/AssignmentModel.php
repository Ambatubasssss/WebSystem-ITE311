<?php

namespace App\Models;

use CodeIgniter\Model;

class AssignmentModel extends Model
{
    protected $table            = 'assignments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['course_id', 'title', 'description', 'due_date', 'total_points', 'assignment_type', 'created_by', 'created_at', 'updated_at'];

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
        'course_id' => 'required|integer',
        'title' => 'required|max_length[255]',
        'total_points' => 'permit_empty|integer',
        'created_by' => 'required|integer'
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
     * Get assignments for a specific course
     */
    public function getAssignmentsByCourse($course_id)
    {
        return $this->select('assignments.*, users.name as created_by_name, courses.title as course_title')
                    ->join('users', 'users.id = assignments.created_by')
                    ->join('courses', 'courses.id = assignments.course_id')
                    ->where('assignments.course_id', $course_id)
                    ->orderBy('assignments.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get assignment with course and creator details
     */
    public function getAssignmentWithDetails($assignment_id)
    {
        return $this->select('assignments.*, users.name as created_by_name, users.email as created_by_email, courses.title as course_title, courses.description as course_description')
                    ->join('users', 'users.id = assignments.created_by')
                    ->join('courses', 'courses.id = assignments.course_id')
                    ->where('assignments.id', $assignment_id)
                    ->first();
    }

    /**
     * Get assignments for courses that a user is enrolled in
     */
    public function getAssignmentsForEnrolledCourses($user_id)
    {
        return $this->select('assignments.*, users.name as created_by_name, courses.title as course_title')
                    ->join('users', 'users.id = assignments.created_by')
                    ->join('courses', 'courses.id = assignments.course_id')
                    ->join('enrollments', 'enrollments.course_id = courses.id')
                    ->where('enrollments.user_id', $user_id)
                    ->orderBy('assignments.due_date', 'ASC')
                    ->orderBy('assignments.created_at', 'DESC')
                    ->findAll();
    }
}
