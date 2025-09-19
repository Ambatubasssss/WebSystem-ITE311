<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyUsersRoleColumn extends Migration
{
    public function up()
    {
        // Modify the existing role column to include teacher and student roles
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'teacher', 'student') DEFAULT 'student'");
        
        // Update existing users with empty roles to 'student'
        $this->db->query("UPDATE users SET role = 'student' WHERE role IS NULL OR role = ''");
    }

    public function down()
    {
        // Revert back to original role column
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user') DEFAULT 'user'");
    }
}
