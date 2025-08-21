<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SubmissionSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'quiz_id'    => 1,
                'user_id'    => 3, // Student One
                'score'      => 18,
                'submitted_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'quiz_id'    => 2,
                'user_id'    => 3,
                'score'      => 22,
                'submitted_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('submissions')->insertBatch($data);
    }
}
