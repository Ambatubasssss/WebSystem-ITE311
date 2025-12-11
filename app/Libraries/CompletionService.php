<?php

namespace App\Libraries;

use App\Models\CourseModel;
use App\Models\EnrollmentModel;
use App\Models\SemesterModel;
use App\Models\NotificationModel;

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
        $today = date('Y-m-d');
        $updatedCourses = [];
        $notifiedSemesters = [];

        // Get all semesters with end dates that have passed
        $db = \Config\Database::connect();
        $semesters = $db->query("
            SELECT * FROM semesters 
            WHERE end_date IS NOT NULL 
            AND end_date <= ?
            ORDER BY end_date DESC
        ", [$today])->getResultArray();

        foreach ($semesters as $semester) {
            // Check if we've already notified about this semester today
            $existingNotifications = $this->notificationModel->where('type', 'semester_completion')
                                                             ->like('message', '%' . ($semester['name'] ?? $semester['semester']) . '%')
                                                             ->where('DATE(created_at)', date('Y-m-d'))
                                                             ->countAllResults();
            
            $shouldNotify = $existingNotifications === 0;

            // Get all courses for this semester that are still active
            $courses = $this->courseModel->where('semester_id', $semester['id'])
                                         ->where('status', 'active')
                                         ->findAll();

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

                    // Notify student about course completion (only if not already notified today)
                    $existingCourseNotification = $this->notificationModel->where('user_id', $enrollment['user_id'])
                                                                          ->where('type', 'completion')
                                                                          ->like('message', $course['title'] . '%')
                                                                          ->where('DATE(created_at)', date('Y-m-d'))
                                                                          ->countAllResults();
                    
                    if ($existingCourseNotification === 0) {
                        $this->notificationModel->insert([
                            'user_id' => $enrollment['user_id'],
                            'message' => "Course '{$course['title']}' has been completed. The {$semester['semester']} Semester ({$semester['term']} Term) has ended.",
                            'type' => 'completion',
                            'is_read' => 0
                        ]);
                    }
                }

                $updatedCourses[] = $course['id'];
            }

            // Notify all students about semester completion (only once per day)
            if ($shouldNotify && !in_array($semester['id'], $notifiedSemesters)) {
                $this->notifySemesterCompletion($semester);
                $notifiedSemesters[] = $semester['id'];
            }
        }

        return [
            'courses_updated' => count($updatedCourses),
            'semesters_processed' => count($notifiedSemesters)
        ];
    }

    /**
     * Notify all students about semester completion
     */
    protected function notifySemesterCompletion($semester)
    {
        $userModel = new \App\Models\UserModel();
        $students = $userModel->where('role', 'student')->findAll();

        $semesterName = $semester['name'] ?? ($semester['semester'] . ' Semester');
        $message = "The {$semesterName} ({$semester['term']} Term) has been completed. All courses for this semester are now marked as completed.";

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
}

