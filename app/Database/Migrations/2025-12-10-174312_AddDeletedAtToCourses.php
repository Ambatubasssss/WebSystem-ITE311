<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToCourses extends Migration
{
    public function up()
    {
        // Check if deleted_at column already exists
        if (!$this->db->fieldExists('deleted_at', 'courses')) {
            $fields = [
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'updated_at',
                ],
            ];
            $this->forge->addColumn('courses', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('deleted_at', 'courses')) {
            $this->forge->dropColumn('courses', 'deleted_at');
        }
    }
}
