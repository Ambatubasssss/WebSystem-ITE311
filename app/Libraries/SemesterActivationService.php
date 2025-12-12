<?php

namespace App\Libraries;

use App\Models\SemesterModel;
use App\Models\AcademicYearModel;

class SemesterActivationService
{
    protected $semesterModel;
    protected $academicYearModel;

    public function __construct()
    {
        $this->semesterModel = new SemesterModel();
        $this->academicYearModel = new AcademicYearModel();
    }

    /**
     * Automatically activate/deactivate semesters and academic years based on current date in Asia/Manila timezone
     * Sequence: 1st Sem 1st Term → 1st Sem 2nd Term → 2nd Sem 1st Term → 2nd Sem 2nd Term → Next Academic Year → repeat
     * This should be called on dashboard load or via cron job
     */
    public function updateActiveSemesters()
    {
        // Set timezone to Asia/Manila
        date_default_timezone_set('Asia/Manila');
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        
        $updatedSemesters = [];
        $updatedAcademicYears = [];
        
        // Get all semesters with start_date and end_date, ordered by start_date
        // This ensures proper sequence: 1st Sem 1st Term → 1st Sem 2nd Term → 2nd Sem 1st Term → 2nd Sem 2nd Term
        $allSemesters = $this->semesterModel->where('start_date IS NOT NULL')
                                           ->where('end_date IS NOT NULL')
                                           ->orderBy('start_date', 'ASC')
                                           ->findAll();
        
        // Find the semester that should be active (current date is between start_date and end_date)
        $activeSemester = null;
        
        foreach ($allSemesters as $semester) {
            $startDate = $semester['start_date'];
            $endDate = $semester['end_date'];
            
            // Check if today is within the semester's date range
            if ($today >= $startDate && $today <= $endDate) {
                $activeSemester = $semester;
                break; // Found the active semester
            }
        }
        
        // If we found an active semester, activate it and deactivate all others
        if ($activeSemester) {
            // Deactivate all semesters first
            $this->semesterModel->set('is_active', 0)->update();
            
            // Activate the current semester
            $this->semesterModel->update($activeSemester['id'], [
                'is_active' => 1,
                'updated_at' => $now
            ]);
            
            $updatedSemesters[] = $activeSemester['id'];
            
            // Also activate the academic year that this semester belongs to
            if (!empty($activeSemester['academic_year_id'])) {
                // Deactivate all academic years first
                $this->academicYearModel->set('is_active', 0)->update();
                
                // Activate the academic year for this semester
                $this->academicYearModel->update($activeSemester['academic_year_id'], [
                    'is_active' => 1,
                    'updated_at' => $now
                ]);
                
                $updatedAcademicYears[] = $activeSemester['academic_year_id'];
                
                log_message('info', 'Semester and Academic Year activated automatically: ' . 
                    ($activeSemester['name'] ?? $activeSemester['semester'] . ' ' . $activeSemester['term']) . 
                    ' (Semester ID: ' . $activeSemester['id'] . ', Academic Year ID: ' . $activeSemester['academic_year_id'] . ')');
            } else {
                log_message('info', 'Semester activated automatically: ' . 
                    ($activeSemester['name'] ?? $activeSemester['semester'] . ' ' . $activeSemester['term']) . 
                    ' (ID: ' . $activeSemester['id'] . ') - No academic year assigned');
            }
        } else {
            // No semester matches the current date - deactivate all semesters and academic years
            // This handles gaps between semesters
            $this->semesterModel->set('is_active', 0)->update();
            $this->academicYearModel->set('is_active', 0)->update();
            log_message('info', 'No active semester found for date: ' . $today . '. All semesters and academic years deactivated.');
        }
        
        // Reset timezone to default (optional, but good practice)
        date_default_timezone_set(date_default_timezone_get());
        
        return [
            'active_semester_id' => $activeSemester ? $activeSemester['id'] : null,
            'active_academic_year_id' => $activeSemester && !empty($activeSemester['academic_year_id']) ? $activeSemester['academic_year_id'] : null,
            'updated_semesters' => count($updatedSemesters),
            'updated_academic_years' => count($updatedAcademicYears)
        ];
    }

    /**
     * Get the currently active semester based on date (without updating database)
     * Useful for checking which semester should be active
     * Returns both the semester and its academic year
     */
    public function getActiveSemesterByDate()
    {
        // Set timezone to Asia/Manila
        date_default_timezone_set('Asia/Manila');
        $today = date('Y-m-d');
        
        // Get all semesters with start_date and end_date, ordered by start_date
        $allSemesters = $this->semesterModel->where('start_date IS NOT NULL')
                                           ->where('end_date IS NOT NULL')
                                           ->orderBy('start_date', 'ASC')
                                           ->findAll();
        
        foreach ($allSemesters as $semester) {
            $startDate = $semester['start_date'];
            $endDate = $semester['end_date'];
            
            // Check if today is within the semester's date range
            if ($today >= $startDate && $today <= $endDate) {
                // Also get the academic year
                $academicYear = null;
                if (!empty($semester['academic_year_id'])) {
                    $academicYear = $this->academicYearModel->find($semester['academic_year_id']);
                }
                
                date_default_timezone_set(date_default_timezone_get());
                return [
                    'semester' => $semester,
                    'academic_year' => $academicYear
                ];
            }
        }
        
        date_default_timezone_set(date_default_timezone_get());
        return null;
    }
}

