<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LessonSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'course_id' => 1,
                'title' => 'HTML Basics',
                'content' => 'Learn HTML structure, tags, and elements for creating web pages.',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'course_id' => 1,
                'title' => 'CSS Styling',
                'content' => 'Master CSS for styling and layout of web pages.',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'course_id' => 2,
                'title' => 'PHP Variables and Data Types',
                'content' => 'Understanding PHP variables, data types, and basic syntax.',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'course_id' => 2,
                'title' => 'PHP Control Structures',
                'content' => 'Learn loops, conditionals, and control flow in PHP.',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'course_id' => 3,
                'title' => 'Database Design Principles',
                'content' => 'Understanding normalization, relationships, and database design.',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('lessons')->insertBatch($data);
    }
}
