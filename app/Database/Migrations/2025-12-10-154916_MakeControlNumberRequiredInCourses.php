<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeControlNumberRequiredInCourses extends Migration
{
    public function up()
    {
        // First, update any NULL control_numbers with a default value based on course ID (4 characters)
        $this->db->query("UPDATE `courses` SET `control_number` = LPAD(`id`, 4, '0') WHERE `control_number` IS NULL OR `control_number` = '' OR LENGTH(`control_number`) != 4");
        
        // Now modify the column to be NOT NULL and exactly 4 characters
        $fields = [
            'control_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '4',
                'null'       => false,
                'unique'     => true,
                'after'      => 'title',
                'comment'    => 'Course Control Number (CN) - Exactly 4 characters, required, unique'
            ],
        ];
        
        $this->forge->modifyColumn('courses', $fields);
    }

    public function down()
    {
        // Revert to nullable
        $fields = [
            'control_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '4',
                'null'       => true,
                'unique'     => true,
                'after'      => 'title',
                'comment'    => 'Course Control Number (CN) - Exactly 4 characters'
            ],
        ];
        
        $this->forge->modifyColumn('courses', $fields);
    }
}
