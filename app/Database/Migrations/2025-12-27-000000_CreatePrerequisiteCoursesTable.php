<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrerequisiteCoursesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'course_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'The course that requires the prerequisite',
            ],
            'prerequisite_course_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'comment'    => 'The prerequisite course that must be completed first',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('course_id', 'courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('prerequisite_course_id', 'courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['course_id', 'prerequisite_course_id'], 'unique_prerequisite');
        $this->forge->createTable('prerequisite_courses');
    }

    public function down()
    {
        $this->forge->dropTable('prerequisite_courses');
    }
}

