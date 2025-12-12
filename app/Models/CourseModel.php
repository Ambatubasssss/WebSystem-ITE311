<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\SemesterModel;
use App\Models\EnrollmentModel;

class CourseModel extends Model
{
    protected $table            = 'courses';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
        protected $allowedFields    = ['title', 'control_number', 'units', 'description', 'academic_year_id', 'semester_id', 'year_level_id', 'status', 'completed_at', 'created_at', 'updated_at', 'deleted_at'];

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
        'control_number' => 'required|exact_length[4]|is_unique[courses.control_number,id,{id}]',
        'units' => 'permit_empty|integer|greater_than_equal_to[0]|less_than_equal_to[5]',
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
     * Get all available courses (exclude completed and deleted)
     */
    public function getAvailableCourses()
    {
        return $this->where('deleted_at', null)
                   ->groupStart()
                   ->where('status !=', 'completed')
                   ->orWhere('status', null)
                   ->groupEnd()
                   ->orderBy('title', 'ASC')
                   ->findAll();
    }

    /**
     * Get courses not enrolled by a specific user
     * Filters by student's year level if they are a student
     * Also filters by term availability: Term 2 courses only available after Term 1 is completed
     */
    public function getCoursesNotEnrolledByUser($user_id, $year_level_id = null)
    {
        $semesterModel = new SemesterModel();
        $enrollmentModel = new EnrollmentModel();
        
        // Build query - filter by year level if provided, exclude completed courses
        $query = $this->where('deleted_at', null)
                     ->groupStart()
                     ->where('status !=', 'completed')
                     ->orWhere('status', null)
                     ->groupEnd();
        
        // If year level is provided, only show courses for that year level
        if ($year_level_id) {
            $query->where('year_level_id', $year_level_id);
        }
        
        // Get all courses matching the criteria
        $allCourses = $query->orderBy('title', 'ASC')->findAll();
        
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
        
        // Filter courses based on:
        // 1. Not enrolled
        // 2. Term availability (Term 2 only available after Term 1 is completed)
        $availableCourses = [];
        
        foreach ($allCourses as $course) {
            // Skip if already enrolled
            if (in_array($course['id'], $enrolledIds)) {
                continue;
            }
            
            // Skip if course is completed (extra safety check)
            if ($course['status'] === 'completed') {
                continue;
            }
            
            // Check term availability
            if ($course['semester_id']) {
                $semester = $semesterModel->find($course['semester_id']);
                if ($semester) {
                    $courseTerm = $semester['term'] ?? '';
                    
                    // If course is Term 2, check if Term 1 is completed
                    if ($courseTerm === '2nd' || $courseTerm === '2') {
                        // Check if there's a Term 1 in the same semester and academic year
                        $term1Semester = $semesterModel->where('semester', $semester['semester'])
                                                       ->groupStart()
                                                       ->where('term', '1st')
                                                       ->orWhere('term', '1')
                                                       ->groupEnd()
                                                       ->where('academic_year_id', $course['academic_year_id'])
                                                       ->first();
                        
                        if ($term1Semester) {
                            // Check if user has completed any Term 1 courses in this semester
                            $term1Courses = $this->where('semester_id', $term1Semester['id'])
                                                 ->where('status', 'completed')
                                                 ->findAll();
                            
                            $hasCompletedTerm1 = false;
                            foreach ($term1Courses as $term1Course) {
                                $enrollment = $enrollmentModel->where('user_id', $user_id)
                                                             ->where('course_id', $term1Course['id'])
                                                             ->where('status', 'completed')
                                                             ->first();
                                if ($enrollment) {
                                    $hasCompletedTerm1 = true;
                                    break;
                                }
                            }
                            
                            // Term 2 is only available if Term 1 is completed
                            if (!$hasCompletedTerm1) {
                                continue; // Skip this Term 2 course
                            }
                        }
                    }
                    // Term 1 courses are always available (no prerequisite term needed)
                }
            }
            
            $availableCourses[] = $course;
        }
        
        return $availableCourses;
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

    /**
     * Get unavailable courses:
     * 1. Term 2 courses waiting for Term 1 completion (same semester, same academic year)
     * 2. Courses with prerequisites that aren't completed (same semester, term, and academic year)
     * 3. Courses from future semesters/terms (start_date > today)
     * 
     * @param int|null $academicYearId Academic year ID to filter by
     * @param int|null $semesterId Semester ID to filter by
     * @param int|null $userId User ID to check enrollment status (required for prerequisite checking)
     */
    public function getUnavailableCourses($academicYearId = null, $semesterId = null, $userId = null)
    {
        try {
            // Set timezone to Asia/Manila for date comparisons
            date_default_timezone_set('Asia/Manila');
            $today = date('Y-m-d');
            
            $semesterModel = new SemesterModel();
            $prerequisiteModel = new \App\Models\PrerequisiteCourseModel();
            $enrollmentModel = new \App\Models\EnrollmentModel();
            
            // Build query for all active courses
            $query = $this->where('deleted_at', null)
                         ->groupStart()
                         ->where('status !=', 'completed')
                         ->orWhere('status', null)
                         ->groupEnd();
            
            if ($academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            }
            
            if ($semesterId) {
                $query->where('semester_id', $semesterId);
            }
            
            $allCourses = $query->orderBy('title', 'ASC')->findAll();
            
            // Return empty array if no courses found
            if (empty($allCourses)) {
                return [];
            }
            
            $unavailableCourses = [];
            $processedCourseIds = []; // To avoid duplicates
        
        foreach ($allCourses as $course) {
            if (in_array($course['id'], $processedCourseIds)) {
                continue; // Skip if already processed
            }
            
            $isUnavailable = false;
            $unavailableReason = '';
            $missingPrerequisites = [];
            
            if (!empty($course['semester_id'])) {
                $semester = $semesterModel->find($course['semester_id']);
                if ($semester) {
                    $semesterStartDate = $semester['start_date'] ?? null;
                    $semesterEndDate = $semester['end_date'] ?? null;
                    $courseTerm = $semester['term'] ?? '';
                    
                    // Check 0: If semester is in the future (start_date > today), it's unavailable
                    if ($semesterStartDate && $semesterStartDate > $today) {
                        $isUnavailable = true;
                        $unavailableReason = 'This course is from a future semester or term (starts on ' . date('M d, Y', strtotime($semesterStartDate)) . ')';
                        $course['semester_info'] = $semester;
                        $course['unavailable_reason'] = $unavailableReason;
                        $unavailableCourses[] = $course;
                        $processedCourseIds[] = $course['id'];
                        continue; // Skip other checks for future courses
                    }
                    
                    // Check 0.5: If semester has ended (end_date < today), it should be in completed, not unavailable
                    // Skip this course from unavailable list if it's from a past semester
                    if ($semesterEndDate && $semesterEndDate < $today) {
                        // This course should be in completed courses, not unavailable
                        continue; // Skip adding to unavailable courses
                    }
                    
                    // Check 1: If this is a Term 2 course, check if Term 1 is completed
                    if ($courseTerm === '2nd' || $courseTerm === '2') {
                        // Check if there's a Term 1 in the same semester and academic year
                        if (!empty($course['academic_year_id']) && !empty($semester['semester'])) {
                            $term1Semester = $semesterModel->where('semester', $semester['semester'])
                                                           ->groupStart()
                                                           ->where('term', '1st')
                                                           ->orWhere('term', '1')
                                                           ->groupEnd()
                                                           ->where('academic_year_id', $course['academic_year_id'])
                                                           ->first();
                            
                            if ($term1Semester) {
                                // Check if Term 1 has any completed courses
                                $term1Courses = $this->where('semester_id', $term1Semester['id'])
                                                     ->where('status', 'completed')
                                                     ->findAll();
                                
                                // If Term 1 has no completed courses, this Term 2 course is unavailable
                                if (empty($term1Courses)) {
                                    $isUnavailable = true;
                                    $unavailableReason = 'Term 1 must be completed first in the same semester and academic year';
                                    $course['required_term'] = 'Term 1';
                                }
                            }
                        }
                    }
                    
                    // Check 2: Check if course has prerequisites that aren't completed
                    try {
                        $prerequisites = $prerequisiteModel->getPrerequisites($course['id']);
                    } catch (\Exception $e) {
                        // If prerequisite table doesn't exist or error, skip prerequisite check
                        log_message('debug', 'Prerequisite check skipped: ' . $e->getMessage());
                        $prerequisites = [];
                    }
                    
                    if (!empty($prerequisites)) {
                        foreach ($prerequisites as $prereq) {
                            if (!empty($prereq['prerequisite_course_id'])) {
                                $prereqCourse = $this->find($prereq['prerequisite_course_id']);
                                if ($prereqCourse && !empty($prereqCourse['semester_id'])) {
                                    $prereqSemester = $semesterModel->find($prereqCourse['semester_id']);
                                    if ($prereqSemester && !empty($semester['semester']) && !empty($semester['term'])) {
                                        // Check if prerequisite is in same semester, term, and academic year
                                        if ($prereqSemester['semester'] === $semester['semester'] &&
                                            $prereqSemester['term'] === $semester['term'] &&
                                            !empty($prereqCourse['academic_year_id']) &&
                                            !empty($course['academic_year_id']) &&
                                            $prereqCourse['academic_year_id'] === $course['academic_year_id']) {
                                            
                                            // Check if user has completed the prerequisite course
                                            // If userId is provided, check user's enrollment; otherwise check course status
                                            $prerequisiteCompleted = false;
                                            
                                            if ($userId) {
                                                // Check if user has completed this prerequisite course
                                                // First check if enrollment exists with 'completed' status
                                                $enrollment = $enrollmentModel->where('user_id', $userId)
                                                                             ->where('course_id', $prereqCourse['id'])
                                                                             ->where('status', 'completed')
                                                                             ->first();
                                                
                                                if ($enrollment) {
                                                    $prerequisiteCompleted = true;
                                                } else {
                                                    // If no completed enrollment, check if course itself is marked as completed
                                                    // This handles cases where course completion is tracked at course level
                                                    $prerequisiteCompleted = (($prereqCourse['status'] ?? null) === 'completed');
                                                }
                                            } else {
                                                // Fallback: check if prerequisite course itself is marked as completed
                                                $prerequisiteCompleted = (($prereqCourse['status'] ?? null) === 'completed');
                                            }
                                            
                                            if (!$prerequisiteCompleted) {
                                                $isUnavailable = true;
                                                $missingPrerequisites[] = [
                                                    'id' => $prereqCourse['id'],
                                                    'title' => $prereqCourse['title'] ?? '',
                                                    'control_number' => $prereqCourse['control_number'] ?? '',
                                                    'semester' => $prereqSemester['semester'] ?? '',
                                                    'term' => $prereqSemester['term'] ?? '',
                                                    'academic_year_id' => $prereqCourse['academic_year_id']
                                                ];
                                                
                                                if (empty($unavailableReason)) {
                                                    $unavailableReason = 'Prerequisite courses must be completed first';
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    // Add to unavailable courses if it meets any criteria
                    if ($isUnavailable) {
                        $course['unavailable_reason'] = $unavailableReason;
                        $course['semester_info'] = $semester;
                        if (!empty($missingPrerequisites)) {
                            $course['missing_prerequisites'] = $missingPrerequisites;
                        }
                        $unavailableCourses[] = $course;
                        $processedCourseIds[] = $course['id'];
                    }
                }
            }
        }
        
        // Reset timezone
        date_default_timezone_set(date_default_timezone_get());
        
        return $unavailableCourses;
        } catch (\Exception $e) {
            // Reset timezone in case of error
            date_default_timezone_set(date_default_timezone_get());
            // Log error and return empty array to prevent breaking the page
            log_message('error', 'Error in getUnavailableCourses: ' . $e->getMessage());
            return [];
        }
    }
}
