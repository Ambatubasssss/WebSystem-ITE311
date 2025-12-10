<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnitsToCourses extends Migration
{
    public function up()
    {
        $fields = [
            'units' => [
                'type'       => 'INT',
                'constraint' => 1,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
                'after'      => 'control_number',
                'comment'    => 'Course units (0-5)'
            ],
        ];
        
        $this->forge->addColumn('courses', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('courses', 'units');
    }
}
