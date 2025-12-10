<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseTeacherModel extends Model
{
    protected $table            = 'course_teachers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
        protected $allowedFields    = ['course_id', 'teacher_id', 'is_primary', 'created_at', 'updated_at', 'deleted_at'];

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
        'teacher_id' => 'required|integer'
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
     * Get teachers for a specific course
     */
    public function getTeachersByCourse($courseId)
    {
        return $this->select('course_teachers.*, users.name as teacher_name, users.email as teacher_email')
                    ->join('users', 'users.id = course_teachers.teacher_id')
                    ->where('course_teachers.course_id', $courseId)
                    ->orderBy('course_teachers.is_primary', 'DESC')
                    ->orderBy('users.name', 'ASC')
                    ->findAll();
    }

    /**
     * Get courses for a specific teacher
     */
    public function getCoursesByTeacher($teacherId)
    {
        return $this->select('course_teachers.*, courses.title, courses.description')
                    ->join('courses', 'courses.id = course_teachers.course_id')
                    ->where('course_teachers.teacher_id', $teacherId)
                    ->orderBy('courses.title', 'ASC')
                    ->findAll();
    }

    /**
     * Check if teacher is assigned to course
     */
    public function isTeacherAssigned($courseId, $teacherId)
    {
        $assignment = $this->where('course_id', $courseId)
                          ->where('teacher_id', $teacherId)
                          ->first();
        
        return $assignment !== null;
    }

    /**
     * Assign teacher to course
     */
    public function assignTeacher($courseId, $teacherId, $isPrimary = false)
    {
        // Check if already assigned
        if ($this->isTeacherAssigned($courseId, $teacherId)) {
            return false;
        }

        // If setting as primary, unset other primary teachers for this course
        if ($isPrimary) {
            $this->where('course_id', $courseId)
                 ->set('is_primary', 0)
                 ->update();
        }

        return $this->insert([
            'course_id' => $courseId,
            'teacher_id' => $teacherId,
            'is_primary' => $isPrimary ? 1 : 0
        ]);
    }

    /**
     * Remove teacher from course
     */
    public function removeTeacher($courseId, $teacherId)
    {
        return $this->where('course_id', $courseId)
                    ->where('teacher_id', $teacherId)
                    ->delete();
    }

    /**
     * Delete all assignments for a course
     */
    public function deleteByCourse($courseId)
    {
        return $this->where('course_id', $courseId)->delete();
    }
}

