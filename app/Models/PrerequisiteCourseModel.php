<?php

namespace App\Models;

use CodeIgniter\Model;

class PrerequisiteCourseModel extends Model
{
    protected $table            = 'prerequisite_courses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['course_id', 'prerequisite_course_id', 'created_at', 'updated_at'];

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
        'prerequisite_course_id' => 'required|integer'
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
     * Get all prerequisites for a course
     */
    public function getPrerequisites($courseId)
    {
        try {
            // Check if table exists
            $db = \Config\Database::connect();
            if (!$db->tableExists('prerequisite_courses')) {
                return [];
            }
            
            return $this->where('course_id', $courseId)
                        ->join('courses', 'courses.id = prerequisite_courses.prerequisite_course_id')
                        ->select('prerequisite_courses.*, courses.title as prerequisite_title, courses.control_number as prerequisite_control_number')
                        ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Error getting prerequisites: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all courses that require this course as a prerequisite
     */
    public function getDependentCourses($prerequisiteCourseId)
    {
        return $this->where('prerequisite_course_id', $prerequisiteCourseId)
                    ->join('courses', 'courses.id = prerequisite_courses.course_id')
                    ->select('prerequisite_courses.*, courses.title as course_title, courses.control_number as course_control_number')
                    ->findAll();
    }

    /**
     * Check if a course has all prerequisites completed for a user
     * Prerequisites must be in the same semester, term, and academic year
     */
    public function arePrerequisitesCompleted($courseId, $userId)
    {
        $prerequisites = $this->where('course_id', $courseId)->findAll();
        
        if (empty($prerequisites)) {
            return true; // No prerequisites, course is available
        }

        $enrollmentModel = new EnrollmentModel();
        $courseModel = new CourseModel();
        
        // Get the course to check its semester, term, and academic year
        $course = $courseModel->find($courseId);
        if (!$course || !$course['semester_id']) {
            return true; // If course has no semester, allow it
        }

        $semesterModel = new SemesterModel();
        $courseSemester = $semesterModel->find($course['semester_id']);
        if (!$courseSemester) {
            return true;
        }

        // Check each prerequisite
        foreach ($prerequisites as $prereq) {
            $prereqCourse = $courseModel->find($prereq['prerequisite_course_id']);
            if (!$prereqCourse || !$prereqCourse['semester_id']) {
                continue; // Skip if prerequisite course not found or has no semester
            }

            $prereqSemester = $semesterModel->find($prereqCourse['semester_id']);
            if (!$prereqSemester) {
                continue;
            }

            // Check if prerequisite is in the same semester, term, and academic year
            if ($prereqSemester['semester'] !== $courseSemester['semester'] ||
                $prereqSemester['term'] !== $courseSemester['term'] ||
                $prereqCourse['academic_year_id'] !== $course['academic_year_id']) {
                // Prerequisite is not in the same semester/term/academic year
                // This prerequisite doesn't apply to this course availability
                continue;
            }

            // Check if user has completed this prerequisite course
            $enrollment = $enrollmentModel->where('user_id', $userId)
                                         ->where('course_id', $prereq['prerequisite_course_id'])
                                         ->where('status', 'completed')
                                         ->first();

            if (!$enrollment) {
                // Prerequisite not completed
                return false;
            }
        }

        return true; // All prerequisites completed
    }

    /**
     * Get prerequisite courses that are not yet available (prerequisites not completed)
     */
    public function getUnavailablePrerequisiteCourses($userId, $academicYearId = null, $semesterId = null)
    {
        $courseModel = new CourseModel();
        $enrollmentModel = new EnrollmentModel();
        $semesterModel = new SemesterModel();
        
        // Get all courses with prerequisites
        $db = \Config\Database::connect();
        $query = "
            SELECT DISTINCT c.*, s.semester, s.term, s.academic_year_id
            FROM courses c
            INNER JOIN prerequisite_courses pc ON pc.course_id = c.id
            LEFT JOIN semesters s ON s.id = c.semester_id
            WHERE c.deleted_at IS NULL
            AND (c.status IS NULL OR c.status != 'completed')
        ";
        
        $params = [];
        if ($academicYearId) {
            $query .= " AND c.academic_year_id = ?";
            $params[] = $academicYearId;
        }
        if ($semesterId) {
            $query .= " AND c.semester_id = ?";
            $params[] = $semesterId;
        }
        
        $courses = $db->query($query, $params)->getResultArray();
        
        $unavailableCourses = [];
        
        foreach ($courses as $course) {
            // Check if prerequisites are completed
            if (!$this->arePrerequisitesCompleted($course['id'], $userId)) {
                // Get prerequisite info
                $prerequisites = $this->getPrerequisites($course['id']);
                $course['prerequisites'] = $prerequisites;
                $course['missing_prerequisites'] = [];
                
                // Check which prerequisites are missing
                foreach ($prerequisites as $prereq) {
                    $prereqCourse = $courseModel->find($prereq['prerequisite_course_id']);
                    if ($prereqCourse && $prereqCourse['semester_id']) {
                        $prereqSemester = $semesterModel->find($prereqCourse['semester_id']);
                        if ($prereqSemester) {
                            // Check if prerequisite is in same semester/term/academic year
                            if ($prereqSemester['semester'] === $course['semester'] &&
                                $prereqSemester['term'] === $course['term'] &&
                                $prereqCourse['academic_year_id'] === $course['academic_year_id']) {
                                
                                // Check if completed
                                $enrollment = $enrollmentModel->where('user_id', $userId)
                                                             ->where('course_id', $prereq['prerequisite_course_id'])
                                                             ->where('status', 'completed')
                                                             ->first();
                                
                                if (!$enrollment) {
                                    $course['missing_prerequisites'][] = [
                                        'id' => $prereqCourse['id'],
                                        'title' => $prereqCourse['title'],
                                        'control_number' => $prereqCourse['control_number'] ?? ''
                                    ];
                                }
                            }
                        }
                    }
                }
                
                if (!empty($course['missing_prerequisites'])) {
                    $unavailableCourses[] = $course;
                }
            }
        }
        
        return $unavailableCourses;
    }
}

