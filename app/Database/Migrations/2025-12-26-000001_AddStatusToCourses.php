<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToCourses extends Migration
{
    public function up()
    {
        $this->forge->addColumn('courses', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'completed', 'cancelled'],
                'default'    => 'active',
                'after'      => 'description',
            ],
            'completed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('courses', ['status', 'completed_at']);
    }
}

