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
                'email'      => 'basteadmin@gmail.com',
                'password'   => password_hash('BasteAdmin123.', PASSWORD_DEFAULT),
                'role'       => 'admin',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Teacher User',
                'email'      => 'basteteacher@gmail.com',
                'password'   => password_hash('BasteTeacher123.', PASSWORD_DEFAULT),
                'role'       => 'teacher',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Student User',
                'email'      => 'bastestudent@gmail.com',
                'password'   => password_hash('BasteStudent123.', PASSWORD_DEFAULT),
                'role'       => 'student',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Clear existing demo accounts first to avoid conflicts
        $this->db->table('users')->whereIn('email', [
            'basteadmin@gmail.com', 
            'basteteacher@gmail.com', 
            'bastestudent@gmail.com'
        ])->delete();

        // Insert fresh demo accounts with correct roles
        $this->db->table('users')->insertBatch($users);
        
        // Double-check roles are set correctly
        $this->db->query("UPDATE users SET role = 'admin' WHERE email = 'basteadmin@gmail.com'");
        $this->db->query("UPDATE users SET role = 'teacher' WHERE email = 'basteteacher@gmail.com'");  
        $this->db->query("UPDATE users SET role = 'student' WHERE email = 'bastestudent@gmail.com'");
    }
}
