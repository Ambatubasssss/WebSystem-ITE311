<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table            = 'courses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['title', 'description', 'created_at', 'updated_at'];

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
        'title' => 'required|min_length[3]|max_length[150]',
        'description' => 'permit_empty|max_length[1000]'
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
     * Get all available courses
     */
    public function getAvailableCourses()
    {
        return $this->orderBy('title', 'ASC')->findAll();
    }

    /**
     * Get courses not enrolled by a specific user
     */
    public function getCoursesNotEnrolledByUser($user_id)
    {
        // Get all courses
        $allCourses = $this->findAll();
        
        // If no courses exist, return empty array
        if (empty($allCourses)) {
            return [];
        }
        
        // Get enrolled course IDs for this user
        $enrolledCourseIds = $this->db->table('enrollments')
                                    ->select('course_id')
                                    ->where('user_id', $user_id)
                                    ->get()
                                    ->getResultArray();
        
        $enrolledIds = array_column($enrolledCourseIds, 'course_id');
        
        // Filter out enrolled courses
        $availableCourses = array_filter($allCourses, function($course) use ($enrolledIds) {
            return !in_array($course['id'], $enrolledIds);
        });
        
        return array_values($availableCourses);
    }

    /**
     * Get course by ID with enrollment count
     */
    public function getCourseWithEnrollmentCount($course_id)
    {
        $enrollmentModel = new EnrollmentModel();
        $course = $this->find($course_id);
        
        if ($course) {
            $course['enrollment_count'] = $enrollmentModel->getCourseEnrollmentCount($course_id);
        }
        
        return $course;
    }

    /**
     * Get popular courses (most enrolled)
     */
    public function getPopularCourses($limit = 5)
    {
        return $this->select('courses.*, COUNT(enrollments.id) as enrollment_count')
                    ->join('enrollments', 'enrollments.course_id = courses.id', 'left')
                    ->groupBy('courses.id')
                    ->orderBy('enrollment_count', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}
