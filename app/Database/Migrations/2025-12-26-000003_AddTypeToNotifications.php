<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTypeToNotifications extends Migration
{
    public function up()
    {
        $this->forge->addColumn('notifications', [
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => 'general',
                'after'      => 'message',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('notifications', ['type']);
    }
}

