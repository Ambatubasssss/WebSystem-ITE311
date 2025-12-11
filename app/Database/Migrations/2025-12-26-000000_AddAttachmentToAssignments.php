<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAttachmentToAssignments extends Migration
{
    public function up()
    {
        $this->forge->addColumn('assignments', [
            'attachment_file_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'description',
            ],
            'attachment_file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'after'      => 'attachment_file_name',
            ],
            'attachment_file_size' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'attachment_file_path',
            ],
            'attachment_file_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'attachment_file_size',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('assignments', ['attachment_file_name', 'attachment_file_path', 'attachment_file_size', 'attachment_file_type']);
    }
}

