<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            'HoD',
            'Professor',
            'Associate Professor',
            'Assistant Professor',
            'Sr. Programmer',
            'Programmer',
            'Computer Operator',
            'Office Subordinate',
            'Lab Incharge',
        ];

        foreach ($designations as $designation) {
            DB::table('designations')->insert([
                'name' => $designation,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
