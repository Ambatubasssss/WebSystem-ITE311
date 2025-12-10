<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToCourseTeachers extends Migration
{
    public function up()
    {
        // Check if deleted_at column already exists
        if (!$this->db->fieldExists('deleted_at', 'course_teachers')) {
            $fields = [
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'updated_at',
                ],
            ];
            $this->forge->addColumn('course_teachers', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('deleted_at', 'course_teachers')) {
            $this->forge->dropColumn('course_teachers', 'deleted_at');
        }
    }
}
