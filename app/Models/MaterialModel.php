<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialModel extends Model
{
    protected $table            = 'materials';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['course_id', 'file_name', 'file_path', 'file_size', 'file_type', 'created_at', 'updated_at'];

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
        'file_name' => 'required|max_length[255]',
        'file_path' => 'required|max_length[255]',
        'file_size' => 'permit_empty|integer',
        'file_type' => 'permit_empty|max_length[100]'
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
     * Insert a new material record
     */
    public function insertMaterial($data)
    {
        return $this->insert($data);
    }

    /**
     * Get all materials for a specific course
     */
    public function getMaterialsByCourse($course_id)
    {
        return $this->where('course_id', $course_id)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get material by ID with course information
     */
    public function getMaterialWithCourse($material_id)
    {
        return $this->select('materials.*, courses.title as course_title')
                    ->join('courses', 'courses.id = materials.course_id')
                    ->where('materials.id', $material_id)
                    ->first();
    }

    /**
     * Delete material and return file path for cleanup
     */
    public function deleteMaterial($material_id)
    {
        $material = $this->find($material_id);
        if ($material) {
            $this->delete($material_id);
            return $material['file_path'];
        }
        return false;
    }

    /**
     * Get materials for courses that a user is enrolled in
     */
    public function getMaterialsForEnrolledCourses($user_id)
    {
        return $this->select('materials.*, courses.title as course_title')
                    ->join('courses', 'courses.id = materials.course_id')
                    ->join('enrollments', 'enrollments.course_id = courses.id')
                    ->where('enrollments.user_id', $user_id)
                    ->orderBy('materials.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Check if user is enrolled in the course of a specific material
     */
    public function isUserEnrolledInMaterialCourse($user_id, $material_id)
    {
        $enrollmentModel = new EnrollmentModel();
        $material = $this->find($material_id);
        
        if (!$material) {
            return false;
        }
        
        return $enrollmentModel->isAlreadyEnrolled($user_id, $material['course_id']);
    }
}
