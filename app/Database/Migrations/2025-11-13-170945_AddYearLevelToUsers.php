<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddYearLevelToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'year_level_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'role',
            ],
        ];
        $this->forge->addColumn('users', $fields);
        
        // Add foreign key constraint
        $this->db->query('ALTER TABLE `users` ADD CONSTRAINT `fk_users_year_level` FOREIGN KEY (`year_level_id`) REFERENCES `year_levels` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        // Remove foreign key constraint
        $this->db->query('ALTER TABLE `users` DROP FOREIGN KEY `fk_users_year_level`');
        
        // Remove column
        $this->forge->dropColumn('users', ['year_level_id']);
    }
}
