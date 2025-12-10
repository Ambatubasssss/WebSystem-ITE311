<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddControlNumberToCourses extends Migration
{
    public function up()
    {
        $fields = [
            'control_number' => [
                'type'       => 'VARCHAR',
                'constraint' => '4',
                'null'       => true,
                'unique'     => true,
                'after'      => 'title',
                'comment'    => 'Course Control Number (CN) - Exactly 4 characters, unique'
            ],
        ];
        
        $this->forge->addColumn('courses', $fields);
        
        // Add unique index on control_number
        $this->db->query('ALTER TABLE `courses` ADD UNIQUE INDEX `idx_control_number` (`control_number`)');
    }

    public function down()
    {
        // Drop unique index first
        $this->db->query('ALTER TABLE `courses` DROP INDEX `idx_control_number`');
        
        // Remove column
        $this->forge->dropColumn('courses', 'control_number');
    }
}
