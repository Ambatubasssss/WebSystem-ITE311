<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $users = [
            [
                'name'       => 'Admin User',
                'email'      => 'admin@example.com',
                'password'   => password_hash('Admin@123', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Teacher User',
                'email'      => 'teacher@example.com',
                'password'   => password_hash('Teacher@123', PASSWORD_DEFAULT),
                'role'       => 'teacher',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Student User',
                'email'      => 'student@example.com',
                'password'   => password_hash('Student@123', PASSWORD_DEFAULT),
                'role'       => 'student',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Clear existing demo accounts first to avoid conflicts
        $this->db->table('users')->whereIn('email', [
            'admin@example.com', 
            'teacher@example.com', 
            'student@example.com'
        ])->delete();

        // Insert fresh demo accounts with correct roles
        $this->db->table('users')->insertBatch($users);
        
        // Double-check roles are set correctly
        $this->db->query("UPDATE users SET role = 'admin' WHERE email = 'admin@example.com'");
        $this->db->query("UPDATE users SET role = 'teacher' WHERE email = 'teacher@example.com'");  
        $this->db->query("UPDATE users SET role = 'student' WHERE email = 'student@example.com'");
    }
}
