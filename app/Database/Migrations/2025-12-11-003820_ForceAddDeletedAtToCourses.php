<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ForceAddDeletedAtToCourses extends Migration
{
    public function up()
    {
        // Force add deleted_at column if it doesn't exist
        if (!$this->db->fieldExists('deleted_at', 'courses')) {
            $this->db->query("ALTER TABLE `courses` ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL AFTER `updated_at`");
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('deleted_at', 'courses')) {
            $this->db->query("ALTER TABLE `courses` DROP COLUMN `deleted_at`");
        }
    }
}
