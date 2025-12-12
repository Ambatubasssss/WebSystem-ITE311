<?php

namespace App\Libraries;

use App\Models\CourseModel;
use App\Models\EnrollmentModel;
use App\Models\SemesterModel;
use App\Models\NotificationModel;
use App\Models\AcademicYearModel;
use App\Models\UserModel;

class CompletionService
{
    protected $courseModel;
    protected $enrollmentModel;
    protected $semesterModel;
    protected $notificationModel;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->semesterModel = new SemesterModel();
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Check and update course and enrollment completion status
     * This should be called periodically (e.g., on dashboard load or via cron)
     */
    public function checkAndUpdateCompletions()
    {
        // Set timezone to Asia/Manila for date comparisons
        date_default_timezone_set('Asia/Manila');
        $today = date('Y-m-d');
        $updatedCourses = [];
        $notifiedSemesters = [];

        // Get all semesters with end dates that have passed AND are NOT active
        // Only complete courses from semesters that are no longer active
        $db = \Config\Database::connect();
        $semesters = $db->query("
            SELECT * FROM semesters 
            WHERE end_date IS NOT NULL 
            AND end_date <= ?
            AND is_active = 0
            ORDER BY end_date DESC
        ", [$today])->getResultArray();

        foreach ($semesters as $semester) {
            // Check if we've already notified about this semester today
            $existingNotifications = $this->notificationModel->where('type', 'semester_completion')
                                                             ->like('message', '%' . ($semester['name'] ?? $semester['semester']) . '%')
                                                             ->where('DATE(created_at)', date('Y-m-d'))
                                                             ->countAllResults();
            
            $shouldNotify = $existingNotifications === 0;

            // Get the semester's term to determine completion logic
            $semesterTerm = $semester['term'] ?? '';
            
            // System now uses only Term 1 and Term 2 (each half semester)
            // - Term 1 = first half of semester
            // - Term 2 = second half of semester
            // When a term ends, only courses in that specific term should be completed
            
            // Get all courses for this semester that are still active (status = null or 'active')
            // New courses have status = null, so we need to check both
            $courses = $this->courseModel->where('semester_id', $semester['id'])
                                         ->groupStart()
                                         ->where('status', 'active')
                                         ->orWhere('status', null)
                                         ->groupEnd()
                                         ->findAll();

            foreach ($courses as $course) {
                // Get the course's semester to check its term
                $courseSemester = $this->semesterModel->find($course['semester_id']);
                if (!$courseSemester) {
                    continue; // Skip if semester not found
                }
                
                $courseTerm = $courseSemester['term'] ?? '';
                
                // Logic: Only complete courses that match the ending term
                // - When Term 1 ends, only complete Term 1 courses
                // - When Term 2 ends, only complete Term 2 courses
                
                if (($semesterTerm === '1st' || $semesterTerm === '1') && 
                    ($courseTerm === '1st' || $courseTerm === '1')) {
                    // Term 1 ending - complete Term 1 courses
                    $this->completeCourse($course, $semester);
                    $updatedCourses[] = $course['id'];
                } elseif (($semesterTerm === '2nd' || $semesterTerm === '2') && 
                          ($courseTerm === '2nd' || $courseTerm === '2')) {
                    // Term 2 ending - complete Term 2 courses
                    $this->completeCourse($course, $semester);
                    $updatedCourses[] = $course['id'];
                }
                // Courses in other terms are not affected
            }

            // Notify all students about semester completion (only once per day)
            if ($shouldNotify && !in_array($semester['id'], $notifiedSemesters)) {
                $this->notifySemesterCompletion($semester);
                $notifiedSemesters[] = $semester['id'];
            }
        }

        // Reset timezone to default
        date_default_timezone_set(date_default_timezone_get());
        
        return [
            'courses_updated' => count($updatedCourses),
            'semesters_processed' => count($notifiedSemesters)
        ];
    }

    /**
     * Helper method to complete a course and its enrollments
     */
    protected function completeCourse($course, $semester)
    {
        // Update course status to completed
        $this->courseModel->update($course['id'], [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s')
        ]);

        // Get all approved enrollments for this course
        $enrollments = $this->enrollmentModel->where('course_id', $course['id'])
                                            ->where('status', 'approved')
                                            ->findAll();

        // Update enrollment status to completed
        foreach ($enrollments as $enrollment) {
            $this->enrollmentModel->update($enrollment['id'], [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ]);

            // Notify student about course completion (only if not already notified today)
            $existingCourseNotification = $this->notificationModel->where('user_id', $enrollment['user_id'])
                                                                  ->where('type', 'completion')
                                                                  ->like('message', $course['title'] . '%')
                                                                  ->where('DATE(created_at)', date('Y-m-d'))
                                                                  ->countAllResults();
            
            if ($existingCourseNotification === 0) {
                $semesterName = ($semester['semester'] ?? '') . ' Semester';
                $termName = ($semester['term'] ?? '') . ' Term';
                $this->notificationModel->insert([
                    'user_id' => $enrollment['user_id'],
                    'message' => "Course '{$course['title']}' has been completed. The {$semesterName} ({$termName}) has ended.",
                    'type' => 'completion',
                    'is_read' => 0
                ]);
            }
        }
        
        // Notify all teachers assigned to this course
        $courseTeacherModel = new \App\Models\CourseTeacherModel();
        $assignedTeachers = $courseTeacherModel->getTeachersByCourse($course['id']);
        
        foreach ($assignedTeachers as $teacherAssignment) {
            if (!empty($teacherAssignment['teacher_id'])) {
                // Check if teacher has already been notified today
                $existingTeacherNotification = $this->notificationModel->where('user_id', $teacherAssignment['teacher_id'])
                                                                       ->where('type', 'course_completion')
                                                                       ->like('message', $course['title'] . '%')
                                                                       ->where('DATE(created_at)', date('Y-m-d'))
                                                                       ->countAllResults();
                
                if ($existingTeacherNotification === 0) {
                    $semesterName = ($semester['semester'] ?? '') . ' Semester';
                    $termName = ($semester['term'] ?? '') . ' Term';
                    $this->notificationModel->insert([
                        'user_id' => $teacherAssignment['teacher_id'],
                        'message' => "Course '{$course['title']}' that you are assigned to teach has been completed. The {$semesterName} ({$termName}) has ended. This course has been removed from your 'My Courses' section.",
                        'type' => 'course_completion',
                        'is_read' => 0
                    ]);
                }
            }
        }
    }

    /**
     * Notify all students about semester completion
     */
    protected function notifySemesterCompletion($semester)
    {
        $userModel = new \App\Models\UserModel();
        $students = $userModel->where('role', 'student')->findAll();

        $semesterName = $semester['name'] ?? ($semester['semester'] . ' Semester');
        $term = $semester['term'] ?? '';
        $message = "The {$semesterName} ({$term} Term) has been completed. All courses for this term are now marked as completed.";

        foreach ($students as $student) {
            $this->notificationModel->insert([
                'user_id' => $student['id'],
                'message' => $message,
                'type' => 'semester_completion',
                'is_read' => 0
            ]);
        }
    }

    /**
     * Notify all users when academic year is opened
     */
    public function notifyAcademicYearOpened($academicYear)
    {
        $userModel = new \App\Models\UserModel();
        // Get all students and teachers
        $students = $userModel->where('role', 'student')->findAll();
        $teachers = $userModel->where('role', 'teacher')->findAll();
        $allUsers = array_merge($students, $teachers);

        $message = "Academic Year {$academicYear['year_start']}-{$academicYear['year_end']} is now open!";

        foreach ($allUsers as $user) {
            $this->notificationModel->insert([
                'user_id' => $user['id'],
                'message' => $message,
                'type' => 'academic_year',
                'is_read' => 0
            ]);
        }
    }

    /**
     * Check and process completed academic years
     * This should be called periodically (e.g., on dashboard load or via cron)
     */
    public function checkAndProcessCompletedAcademicYears()
    {
        $currentYear = (int) date('Y');
        $academicYearModel = new AcademicYearModel();
        $userModel = new UserModel();
        
        // Get all academic years where year_end has passed
        $completedAcademicYears = $academicYearModel->where('year_end <', $currentYear)
                                                    ->where('is_active', 0) // Only process inactive ones
                                                    ->findAll();
        
        $processedYears = [];
        
        foreach ($completedAcademicYears as $academicYear) {
            // Check if we've already notified about this academic year today
            $existingNotifications = $this->notificationModel->where('type', 'academic_year_completion')
                                                             ->like('message', '%' . $academicYear['year_start'] . '-' . $academicYear['year_end'] . '%')
                                                             ->where('DATE(created_at)', date('Y-m-d'))
                                                             ->countAllResults();
            
            if ($existingNotifications > 0) {
                continue; // Already processed today
            }
            
            // Get all courses for this academic year that are not yet completed
            $courses = $this->courseModel->where('academic_year_id', $academicYear['id'])
                                        ->groupStart()
                                        ->where('status !=', 'completed')
                                        ->orWhere('status', null)
                                        ->groupEnd()
                                        ->where('deleted_at', null)
                                        ->findAll();
            
            $completedCoursesCount = 0;
            
            // Mark all courses as completed and archive them
            foreach ($courses as $course) {
                // Update course status to completed
                $this->courseModel->update($course['id'], [
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s')
                ]);
                
                // Get all approved enrollments for this course
                $enrollments = $this->enrollmentModel->where('course_id', $course['id'])
                                                    ->where('status', 'approved')
                                                    ->findAll();
                
                // Update enrollment status to completed
                foreach ($enrollments as $enrollment) {
                    $this->enrollmentModel->update($enrollment['id'], [
                        'status' => 'completed',
                        'completed_at' => date('Y-m-d H:i:s')
                    ]);
                }
                
                $completedCoursesCount++;
            }
            
            // Notify all users about academic year completion
            $this->notifyAcademicYearCompletion($academicYear, $completedCoursesCount);
            
            $processedYears[] = $academicYear['id'];
        }
        
        return [
            'academic_years_processed' => count($processedYears),
            'years' => $processedYears
        ];
    }

    /**
     * Notify all users about academic year completion
     */
    protected function notifyAcademicYearCompletion($academicYear, $coursesCount = 0)
    {
        $userModel = new UserModel();
        
        // Get all users (students, teachers, and admins)
        $allUsers = $userModel->whereIn('role', ['student', 'teacher', 'admin'])->findAll();
        
        $yearRange = $academicYear['year_start'] . '-' . $academicYear['year_end'];
        $message = "Academic Year {$yearRange} has been completed! ";
        
        if ($coursesCount > 0) {
            $message .= "{$coursesCount} course(s) from this academic year have been moved to completed courses.";
        } else {
            $message .= "All courses from this academic year have been archived.";
        }
        
        foreach ($allUsers as $user) {
            $this->notificationModel->insert([
                'user_id' => $user['id'],
                'message' => $message,
                'type' => 'academic_year_completion',
                'is_read' => 0
            ]);
        }
    }

    /**
     * Get completed courses for a specific academic year
     */
    public function getCompletedCoursesByAcademicYear($academicYearId)
    {
        return $this->courseModel->where('academic_year_id', $academicYearId)
                                 ->where('status', 'completed')
                                 ->where('deleted_at', null)
                                 ->orderBy('completed_at', 'DESC')
                                 ->findAll();
    }

    /**
     * Archive all courses from a specific academic year
     * This is called when an academic year is deactivated (when a new one is activated)
     */
    public function archiveAcademicYearCourses($academicYearId)
    {
        $academicYearModel = new AcademicYearModel();
        $academicYear = $academicYearModel->find($academicYearId);
        
        if (!$academicYear) {
            return [
                'success' => false,
                'message' => 'Academic year not found.',
                'courses_archived' => 0
            ];
        }
        
        // Get all courses for this academic year that are not yet completed
        $courses = $this->courseModel->where('academic_year_id', $academicYearId)
                                    ->groupStart()
                                    ->where('status !=', 'completed')
                                    ->orWhere('status', null)
                                    ->groupEnd()
                                    ->where('deleted_at', null)
                                    ->findAll();
        
        $completedCoursesCount = 0;
        
        // Mark all courses as completed and archive them
        foreach ($courses as $course) {
            // Update course status to completed
            $this->courseModel->update($course['id'], [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ]);
            
            // Get all approved enrollments for this course
            $enrollments = $this->enrollmentModel->where('course_id', $course['id'])
                                                ->where('status', 'approved')
                                                ->findAll();
            
            // Update enrollment status to completed
            foreach ($enrollments as $enrollment) {
                $this->enrollmentModel->update($enrollment['id'], [
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $completedCoursesCount++;
        }
        
        // Notify all users about academic year archiving
        if ($completedCoursesCount > 0) {
            $this->notifyAcademicYearArchived($academicYear, $completedCoursesCount);
        }
        
        return [
            'success' => true,
            'courses_archived' => $completedCoursesCount,
            'academic_year' => $academicYear
        ];
    }

    /**
     * Notify all users about academic year archiving (when deactivated)
     */
    protected function notifyAcademicYearArchived($academicYear, $coursesCount = 0)
    {
        $userModel = new UserModel();
        
        // Get all users (students, teachers, and admins)
        $allUsers = $userModel->whereIn('role', ['student', 'teacher', 'admin'])->findAll();
        
        $yearRange = $academicYear['year_start'] . '-' . $academicYear['year_end'];
        $message = "Academic Year {$yearRange} has been archived. ";
        
        if ($coursesCount > 0) {
            $message .= "{$coursesCount} course(s) from this academic year have been moved to completed courses.";
        } else {
            $message .= "All courses from this academic year have been archived.";
        }
        
        foreach ($allUsers as $user) {
            $this->notificationModel->insert([
                'user_id' => $user['id'],
                'message' => $message,
                'type' => 'academic_year_archived',
                'is_read' => 0
            ]);
        }
    }

    /**
     * Reactivate all completed courses from a specific academic year
     * This is called when an academic year is reactivated (set back to active)
     */
    public function reactivateAcademicYearCourses($academicYearId)
    {
        $academicYearModel = new AcademicYearModel();
        $academicYear = $academicYearModel->find($academicYearId);
        
        if (!$academicYear) {
            return [
                'success' => false,
                'message' => 'Academic year not found.',
                'courses_reactivated' => 0
            ];
        }
        
        // Get all completed courses for this academic year
        $courses = $this->courseModel->where('academic_year_id', $academicYearId)
                                    ->where('status', 'completed')
                                    ->where('deleted_at', null)
                                    ->findAll();
        
        $reactivatedCoursesCount = 0;
        
        // Reactivate all courses
        foreach ($courses as $course) {
            // Update course status back to active (null or 'active')
            $this->courseModel->update($course['id'], [
                'status' => null,
                'completed_at' => null
            ]);
            
            // Get all completed enrollments for this course
            $enrollments = $this->enrollmentModel->where('course_id', $course['id'])
                                                ->where('status', 'completed')
                                                ->findAll();
            
            // Reactivate enrollments back to approved status
            foreach ($enrollments as $enrollment) {
                $this->enrollmentModel->update($enrollment['id'], [
                    'status' => 'approved',
                    'completed_at' => null
                ]);
            }
            
            $reactivatedCoursesCount++;
        }
        
        // Notify all users about academic year reactivation
        if ($reactivatedCoursesCount > 0) {
            $this->notifyAcademicYearReactivated($academicYear, $reactivatedCoursesCount);
        }
        
        return [
            'success' => true,
            'courses_reactivated' => $reactivatedCoursesCount,
            'academic_year' => $academicYear
        ];
    }

    /**
     * Notify all users about academic year reactivation
     */
    protected function notifyAcademicYearReactivated($academicYear, $coursesCount = 0)
    {
        $userModel = new UserModel();
        
        // Get all users (students, teachers, and admins)
        $allUsers = $userModel->whereIn('role', ['student', 'teacher', 'admin'])->findAll();
        
        $yearRange = $academicYear['year_start'] . '-' . $academicYear['year_end'];
        $message = "Academic Year {$yearRange} has been reactivated. ";
        
        if ($coursesCount > 0) {
            $message .= "{$coursesCount} course(s) from this academic year have been reactivated and moved back to active courses.";
        } else {
            $message .= "This academic year is now active.";
        }
        
        foreach ($allUsers as $user) {
            $this->notificationModel->insert([
                'user_id' => $user['id'],
                'message' => $message,
                'type' => 'academic_year_reactivated',
                'is_read' => 0
            ]);
        }
    }

    /**
     * Archive all courses from a specific semester
     * This is called when a semester is deactivated (when a new one is activated)
     */
    public function archiveSemesterCourses($semesterId)
    {
        $semesterModel = new \App\Models\SemesterModel();
        $semester = $semesterModel->find($semesterId);
        
        if (!$semester) {
            return [
                'success' => false,
                'message' => 'Semester not found.',
                'courses_archived' => 0
            ];
        }
        
        // Get all courses for this semester that are not yet completed
        $courses = $this->courseModel->where('semester_id', $semesterId)
                                    ->groupStart()
                                    ->where('status !=', 'completed')
                                    ->orWhere('status', null)
                                    ->groupEnd()
                                    ->where('deleted_at', null)
                                    ->findAll();
        
        $completedCoursesCount = 0;
        
        // Mark all courses as completed and archive them
        foreach ($courses as $course) {
            // Update course status to completed
            $this->courseModel->update($course['id'], [
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ]);
            
            // Get all approved enrollments for this course
            $enrollments = $this->enrollmentModel->where('course_id', $course['id'])
                                                ->where('status', 'approved')
                                                ->findAll();
            
            // Update enrollment status to completed
            foreach ($enrollments as $enrollment) {
                $this->enrollmentModel->update($enrollment['id'], [
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $completedCoursesCount++;
        }
        
        // Notify all users about semester archiving
        if ($completedCoursesCount > 0) {
            $this->notifySemesterArchived($semester, $completedCoursesCount);
        }
        
        return [
            'success' => true,
            'courses_archived' => $completedCoursesCount,
            'semester' => $semester
        ];
    }

    /**
     * Reactivate all completed courses from a specific semester
     * This is called when a semester is reactivated (set back to active)
     */
    public function reactivateSemesterCourses($semesterId)
    {
        $semesterModel = new \App\Models\SemesterModel();
        $semester = $semesterModel->find($semesterId);
        
        if (!$semester) {
            return [
                'success' => false,
                'message' => 'Semester not found.',
                'courses_reactivated' => 0
            ];
        }
        
        // Get all completed courses for this semester
        $courses = $this->courseModel->where('semester_id', $semesterId)
                                    ->where('status', 'completed')
                                    ->where('deleted_at', null)
                                    ->findAll();
        
        $reactivatedCoursesCount = 0;
        
        // Reactivate all courses
        foreach ($courses as $course) {
            // Update course status back to active (null or 'active')
            $this->courseModel->update($course['id'], [
                'status' => null,
                'completed_at' => null
            ]);
            
            // Get all completed enrollments for this course
            $enrollments = $this->enrollmentModel->where('course_id', $course['id'])
                                                ->where('status', 'completed')
                                                ->findAll();
            
            // Reactivate enrollments back to approved status
            foreach ($enrollments as $enrollment) {
                $this->enrollmentModel->update($enrollment['id'], [
                    'status' => 'approved',
                    'completed_at' => null
                ]);
            }
            
            $reactivatedCoursesCount++;
        }
        
        // Notify all users about semester reactivation
        if ($reactivatedCoursesCount > 0) {
            $this->notifySemesterReactivated($semester, $reactivatedCoursesCount);
        }
        
        return [
            'success' => true,
            'courses_reactivated' => $reactivatedCoursesCount,
            'semester' => $semester
        ];
    }

    /**
     * Notify all users about semester archiving (when deactivated)
     */
    protected function notifySemesterArchived($semester, $coursesCount = 0)
    {
        $userModel = new UserModel();
        
        // Get all users (students, teachers, and admins)
        $allUsers = $userModel->whereIn('role', ['student', 'teacher', 'admin'])->findAll();
        
        $semesterName = $semester['name'] ?? ($semester['semester'] . ' Semester');
        $term = $semester['term'] ?? '';
        $message = "Semester '{$semesterName}' ({$term} Term) has been archived. ";
        
        if ($coursesCount > 0) {
            $message .= "{$coursesCount} course(s) from this semester have been moved to completed courses.";
        } else {
            $message .= "All courses from this semester have been archived.";
        }
        
        foreach ($allUsers as $user) {
            $this->notificationModel->insert([
                'user_id' => $user['id'],
                'message' => $message,
                'type' => 'semester_archived',
                'is_read' => 0
            ]);
        }
    }

    /**
     * Notify all users about semester reactivation
     */
    protected function notifySemesterReactivated($semester, $coursesCount = 0)
    {
        $userModel = new UserModel();
        
        // Get all users (students, teachers, and admins)
        $allUsers = $userModel->whereIn('role', ['student', 'teacher', 'admin'])->findAll();
        
        $semesterName = $semester['name'] ?? ($semester['semester'] . ' Semester');
        $term = $semester['term'] ?? '';
        $message = "Semester '{$semesterName}' ({$term} Term) has been reactivated. ";
        
        if ($coursesCount > 0) {
            $message .= "{$coursesCount} course(s) from this semester have been reactivated and moved back to active courses.";
        } else {
            $message .= "This semester is now active.";
        }
        
        foreach ($allUsers as $user) {
            $this->notificationModel->insert([
                'user_id' => $user['id'],
                'message' => $message,
                'type' => 'semester_reactivated',
                'is_read' => 0
            ]);
        }
    }
}

