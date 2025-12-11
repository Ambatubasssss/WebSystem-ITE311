<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompletionStatusToEnrollments extends Migration
{
    public function up()
    {
        // Check if status column exists (from previous migration)
        $fields = $this->db->getFieldNames('enrollments');
        $hasStatusColumn = in_array('status', $fields);
        
        if ($hasStatusColumn) {
            // Update existing status ENUM to include 'completed'
            $this->db->query("ALTER TABLE `enrollments` MODIFY COLUMN `status` ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending'");
        } else {
            // Add status column if it doesn't exist
            $this->forge->addColumn('enrollments', [
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['pending', 'approved', 'rejected', 'completed'],
                    'default'    => 'pending',
                    'after'      => 'enrollment_date',
                ],
            ]);
        }
        
        // Add completion fields
        $this->forge->addColumn('enrollments', [
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'rejection_reason',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('enrollments', ['completed_at']);
        // Note: We don't remove status column as it might be from previous migration
    }
}

