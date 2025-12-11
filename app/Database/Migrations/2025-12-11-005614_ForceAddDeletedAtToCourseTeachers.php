<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ForceAddDeletedAtToCourseTeachers extends Migration
{
    public function up()
    {
        // Force add deleted_at column if it doesn't exist
        if (!$this->db->fieldExists('deleted_at', 'course_teachers')) {
            $this->db->query("ALTER TABLE `course_teachers` ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL AFTER `updated_at`");
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('deleted_at', 'course_teachers')) {
            $this->db->query("ALTER TABLE `course_teachers` DROP COLUMN `deleted_at`");
        }
    }
}
