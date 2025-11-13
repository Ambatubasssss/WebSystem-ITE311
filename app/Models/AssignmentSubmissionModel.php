<?php

namespace App\Models;

use CodeIgniter\Model;

class AssignmentSubmissionModel extends Model
{
    protected $table            = 'assignment_submissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['assignment_id', 'user_id', 'submission_type', 'text_entry', 'file_name', 'file_path', 'file_size', 'file_type', 'submitted_at', 'score', 'feedback', 'graded_at', 'graded_by', 'created_at', 'updated_at'];

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
        'assignment_id' => 'required|integer',
        'user_id' => 'required|integer',
        'submission_type' => 'required|in_list[text,file]',
        'file_name' => 'permit_empty|max_length[255]',
        'file_path' => 'permit_empty|max_length[500]',
        'text_entry' => 'permit_empty',
        'submitted_at' => 'required|valid_date'
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
     * Get submissions for a specific assignment
     */
    public function getSubmissionsByAssignment($assignment_id)
    {
        return $this->select('assignment_submissions.*, users.name as student_name, users.email as student_email')
                    ->join('users', 'users.id = assignment_submissions.user_id')
                    ->where('assignment_submissions.assignment_id', $assignment_id)
                    ->orderBy('assignment_submissions.submitted_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get submission by user and assignment
     */
    public function getSubmissionByUserAndAssignment($user_id, $assignment_id)
    {
        return $this->where('user_id', $user_id)
                    ->where('assignment_id', $assignment_id)
                    ->first();
    }

    /**
     * Check if user has already submitted
     */
    public function hasUserSubmitted($user_id, $assignment_id)
    {
        $submission = $this->getSubmissionByUserAndAssignment($user_id, $assignment_id);
        return $submission !== null;
    }

    /**
     * Get submission with assignment details
     */
    public function getSubmissionWithDetails($submission_id)
    {
        return $this->select('assignment_submissions.*, users.name as student_name, users.email as student_email, assignments.title as assignment_title, assignments.course_id, courses.title as course_title')
                    ->join('users', 'users.id = assignment_submissions.user_id')
                    ->join('assignments', 'assignments.id = assignment_submissions.assignment_id')
                    ->join('courses', 'courses.id = assignments.course_id')
                    ->where('assignment_submissions.id', $submission_id)
                    ->first();
    }
}
