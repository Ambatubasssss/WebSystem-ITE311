<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyUsersRoleColumn extends Migration
{
    public function up()
    {
        // Ensure role column has the correct ENUM values
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('admin','teacher','student') NOT NULL DEFAULT 'student'");

        // Normalize and fix any existing data (in case of restores)
        $this->db->query("UPDATE users SET role = LOWER(TRIM(role))");
        $this->db->query("UPDATE users SET role = 'student' WHERE role IS NULL OR role = '' OR role NOT IN ('admin','teacher','student')");

        // Ensure demo accounts (if present) have correct roles
        $this->db->query("UPDATE users SET role = 'admin'   WHERE email = 'admin@example.com'");
        $this->db->query("UPDATE users SET role = 'teacher' WHERE email = 'teacher@example.com'");
        $this->db->query("UPDATE users SET role = 'student' WHERE email = 'student@example.com'");
    }

    public function down()
    {
        // Revert back to original role column
        $this->db->query("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user') DEFAULT 'user'");
    }
}
