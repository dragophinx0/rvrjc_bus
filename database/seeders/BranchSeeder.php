<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = [
            // B.Tech branches
            ['name' => 'Civil Engineering', 'code' => 'BTECH-CE', 'course_type' => 'B.Tech'],
            ['name' => 'Mechanical Engineering', 'code' => 'BTECH-ME', 'course_type' => 'B.Tech'],
            ['name' => 'Electronics & Communication Engineering', 'code' => 'BTECH-ECE', 'course_type' => 'B.Tech'],
            ['name' => 'Computer Science & Engineering', 'code' => 'BTECH-CSE', 'course_type' => 'B.Tech'],
            ['name' => 'Electrical & Electronics Engineering', 'code' => 'BTECH-EEE', 'course_type' => 'B.Tech'],
            ['name' => 'Chemical Engineering', 'code' => 'BTECH-CHE', 'course_type' => 'B.Tech'],
            ['name' => 'Information Technology', 'code' => 'BTECH-IT', 'course_type' => 'B.Tech'],
            ['name' => 'Computer Science & Business Systems', 'code' => 'BTECH-CSBS', 'course_type' => 'B.Tech'],
            ['name' => 'Computer Science & Engineering - AI & ML', 'code' => 'BTECH-CSM', 'course_type' => 'B.Tech'],
            ['name' => 'Computer Science & Engineering - Data Science', 'code' => 'BTECH-CSD', 'course_type' => 'B.Tech'],
            ['name' => 'Computer Science & Engineering - IOT', 'code' => 'BTECH-CSO', 'course_type' => 'B.Tech'],

            // M.Tech branches
            ['name' => 'Computer Science & Engineering', 'code' => 'MTECH-CSE', 'course_type' => 'M.Tech'],
            ['name' => 'Structural Engineering', 'code' => 'MTECH-SE', 'course_type' => 'M.Tech'],
            ['name' => 'Power Systems Engineering', 'code' => 'MTECH-PSE', 'course_type' => 'M.Tech'],
            ['name' => 'VLSI', 'code' => 'MTECH-VLSI', 'course_type' => 'M.Tech'],
            ['name' => 'Machine Design', 'code' => 'MTECH-MD', 'course_type' => 'M.Tech'],
            ['name' => 'Artificial Intelligence and Data Science', 'code' => 'MTECH-AIDS', 'course_type' => 'M.Tech'],

            // MBA, BBA, MCA (no specific branches, just the course itself)
            ['name' => 'Business Administration', 'code' => 'MBA', 'course_type' => 'MBA'],
            ['name' => 'Business Administration', 'code' => 'BBA', 'course_type' => 'BBA'],
            ['name' => 'Computer Applications', 'code' => 'MCA', 'course_type' => 'MCA'],
        ];

        foreach ($branches as $branch) {
            DB::table('branches')->insert([
                'name' => $branch['name'],
                'code' => $branch['code'],
                'course_type' => $branch['course_type'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
