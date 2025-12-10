<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsActiveToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'is_active' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'comment' => '1 = Active, 0 = Deactivated'
            ]
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'is_active');
    }
}
