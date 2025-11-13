<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGradingFieldsToSubmissions extends Migration
{
    public function up()
    {
        $fields = [
            'score' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'after'      => 'submitted_at',
            ],
            'feedback' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'score',
            ],
            'graded_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'feedback',
            ],
            'graded_by' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'graded_at',
            ],
        ];
        $this->forge->addColumn('assignment_submissions', $fields);
        
        // Add foreign key constraint for graded_by
        $this->db->query('ALTER TABLE `assignment_submissions` ADD CONSTRAINT `fk_submissions_graded_by` FOREIGN KEY (`graded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        // Remove foreign key constraint
        $this->db->query('ALTER TABLE `assignment_submissions` DROP FOREIGN KEY `fk_submissions_graded_by`');
        
        // Remove columns
        $this->forge->dropColumn('assignment_submissions', ['score', 'feedback', 'graded_at', 'graded_by']);
    }
}
