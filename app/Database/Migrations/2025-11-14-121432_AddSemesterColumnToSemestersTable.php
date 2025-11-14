<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSemesterColumnToSemestersTable extends Migration
{
    public function up()
    {
        // Check if semester column exists, if not add it
        $fields = [
            'semester' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => '1st',
                'after'      => 'name',
                'comment'    => '1st Semester or 2nd Semester within the academic year',
            ],
        ];
        
        // Add semester column if it doesn't exist
        if (!$this->db->fieldExists('semester', 'semesters')) {
            $this->forge->addColumn('semesters', $fields);
        }
        
        // Update existing records to have default semester value based on name
        $this->db->query("UPDATE semesters SET semester = '1st' WHERE (name LIKE '%First%' OR name LIKE '%1st%') AND (semester IS NULL OR semester = '')");
        $this->db->query("UPDATE semesters SET semester = '2nd' WHERE (name LIKE '%Second%' OR name LIKE '%2nd%') AND (semester IS NULL OR semester = '')");
        $this->db->query("UPDATE semesters SET semester = '1st' WHERE semester IS NULL OR semester = ''");
        
        // Convert term column from ENUM to VARCHAR if it's ENUM
        // First check the column type
        $db = \Config\Database::connect();
        $query = $db->query("SHOW COLUMNS FROM semesters WHERE Field = 'term'");
        $result = $query->getRow();
        
        if ($result && strpos(strtolower($result->Type), 'enum') !== false) {
            // Convert ENUM to VARCHAR
            $this->db->query("ALTER TABLE semesters MODIFY COLUMN term VARCHAR(10) DEFAULT '1st'");
            
            // Update 'Summer' to '3rd' if it exists
            $this->db->query("UPDATE semesters SET term = '3rd' WHERE term = 'Summer'");
        }
    }

    public function down()
    {
        // Remove semester column
        if ($this->db->fieldExists('semester', 'semesters')) {
            $this->forge->dropColumn('semesters', ['semester']);
        }
    }
}
