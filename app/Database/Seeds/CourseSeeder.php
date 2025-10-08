<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $courses = [
            [
                'title'       => 'Introduction to Programming',
                'description' => 'Learn the fundamentals of programming with hands-on exercises and real-world projects.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'       => 'Web Development Basics',
                'description' => 'Master HTML, CSS, and JavaScript to build responsive and interactive websites.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'       => 'Database Management',
                'description' => 'Learn SQL, database design, and data management best practices.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'       => 'Mobile App Development',
                'description' => 'Create mobile applications using modern frameworks and development tools.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'       => 'Cybersecurity Fundamentals',
                'description' => 'Understand security threats, vulnerabilities, and protection strategies.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title'       => 'Data Science and Analytics',
                'description' => 'Explore data analysis, machine learning, and statistical modeling techniques.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Clear existing courses first (handle foreign key constraints)
        $this->db->table('enrollments')->where('id >', 0)->delete();
        $this->db->table('courses')->where('id >', 0)->delete();

        // Insert sample courses
        $this->db->table('courses')->insertBatch($courses);
    }
}