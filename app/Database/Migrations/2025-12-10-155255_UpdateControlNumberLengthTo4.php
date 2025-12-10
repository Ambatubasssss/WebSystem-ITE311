<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateControlNumberLengthTo4 extends Migration
{
    public function up()
    {
        // Update any existing control_numbers that are not 4 characters
        // Truncate longer ones to 4 chars, pad shorter ones
        $this->db->query("UPDATE `courses` SET `control_number` = LEFT(LPAD(`control_number`, 4, '0'), 4) WHERE LENGTH(`control_number`) != 4");
        
        // Modify the column to be exactly 4 characters
        $this->db->query("ALTER TABLE `courses` MODIFY COLUMN `control_number` VARCHAR(4) NOT NULL");
    }

    public function down()
    {
        // Revert to 50 characters (but keep NOT NULL)
        $this->db->query("ALTER TABLE `courses` MODIFY COLUMN `control_number` VARCHAR(50) NOT NULL");
    }
}
