<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNormalizationToCourses extends Migration
{
    public function up()
    {
        // Add foreign keys to courses table for normalization
        $fields = [
            'academic_year_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'description',
            ],
            'semester_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'academic_year_id',
            ],
            'year_level_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'semester_id',
            ],
        ];
        
        $this->forge->addColumn('courses', $fields);
        
        // Add foreign key constraints
        $this->db->query('ALTER TABLE `courses` ADD CONSTRAINT `fk_courses_academic_year` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `courses` ADD CONSTRAINT `fk_courses_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `courses` ADD CONSTRAINT `fk_courses_year_level` FOREIGN KEY (`year_level_id`) REFERENCES `year_levels` (`id`) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        // Remove foreign key constraints
        $this->db->query('ALTER TABLE `courses` DROP FOREIGN KEY `fk_courses_academic_year`');
        $this->db->query('ALTER TABLE `courses` DROP FOREIGN KEY `fk_courses_semester`');
        $this->db->query('ALTER TABLE `courses` DROP FOREIGN KEY `fk_courses_year_level`');
        
        // Remove columns
        $this->forge->dropColumn('courses', ['academic_year_id', 'semester_id', 'year_level_id']);
    }
}
