<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApprovalStatusToEnrollments extends Migration
{
    public function up()
    {
        $fields = [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default' => 'pending',
                'after' => 'enrollment_date'
            ],
            'approved_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'status'
            ],
            'approved_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'approved_by'
            ],
            'rejected_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'approved_at'
            ],
            'rejected_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'rejected_by'
            ],
            'rejection_reason' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'rejected_at'
            ]
        ];

        $this->forge->addColumn('enrollments', $fields);
        
        // Add foreign key for approved_by
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'CASCADE', 'SET NULL', 'enrollments_approved_by_fk');
        
        // Add foreign key for rejected_by
        $this->forge->addForeignKey('rejected_by', 'users', 'id', 'CASCADE', 'SET NULL', 'enrollments_rejected_by_fk');
    }

    public function down()
    {
        // Drop foreign keys first
        $this->forge->dropForeignKey('enrollments', 'enrollments_approved_by_fk');
        $this->forge->dropForeignKey('enrollments', 'enrollments_rejected_by_fk');
        
        // Drop columns
        $this->forge->dropColumn('enrollments', ['status', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason']);
    }
}

