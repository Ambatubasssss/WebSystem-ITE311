<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'title' => 'Welcome to the New Academic Year 2025-2026',
                'content' => 'We are excited to welcome all students, teachers, and staff to the new academic year. This year brings new opportunities for learning and growth. Please make sure to check your course schedules and familiarize yourself with the updated portal features.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            [
                'title' => 'Important: Midterm Examination Schedule',
                'content' => 'The midterm examinations will be conducted from October 20-25, 2025. All students are required to check their examination schedules and prepare accordingly. Please ensure you have all necessary materials and arrive on time for your exams.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
            ]
        ];

        // Insert sample announcements
        $this->db->table('announcements')->insertBatch($data);
    }
}
