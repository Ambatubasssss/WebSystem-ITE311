<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseScheduleModel extends Model
{
    protected $table            = 'course_schedules';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['course_id', 'day_of_week', 'start_time', 'end_time', 'room', 'created_at', 'updated_at'];

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
        'day_of_week' => 'required|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
        'start_time' => 'required',
        'end_time' => 'required'
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
     * Get schedules for a specific course
     */
    public function getSchedulesByCourse($courseId)
    {
        return $this->where('course_id', $courseId)
                    ->orderBy('day_of_week', 'ASC')
                    ->orderBy('start_time', 'ASC')
                    ->findAll();
    }

    /**
     * Delete all schedules for a course
     */
    public function deleteByCourse($courseId)
    {
        return $this->where('course_id', $courseId)->delete();
    }
}


