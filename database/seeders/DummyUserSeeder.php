<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\Designation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Password@123');

        // 1. Bus Coordinator
        $coordinator = User::create([
            'name' => 'John Coordinator',
            'first_name' => 'John',
            'last_name' => 'Coordinator',
            'gender' => 'male',
            'email' => 'coordinator@rvrjc.ac.in',
            'password' => $password,
            'mobile_number' => '9000000001',
            'role' => User::ROLE_COORDINATOR,
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // 2. Driver
        $driver = User::create([
            'name' => 'Mike Driver',
            'first_name' => 'Mike',
            'last_name' => 'Driver',
            'gender' => 'male',
            'email' => 'driver@rvrjc.ac.in',
            'password' => $password,
            'mobile_number' => '9000000002',
            'role' => User::ROLE_DRIVER,
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // 3. Faculty
        $branch = Branch::first();
        $designation = Designation::first();

        $faculty = User::create([
            'name' => 'Sarah Professor',
            'first_name' => 'Sarah',
            'last_name' => 'Professor',
            'gender' => 'female',
            'email' => 'faculty@rvrjc.ac.in',
            'password' => $password,
            'mobile_number' => '9000000003',
            'role' => User::ROLE_FACULTY,
            'employee_id' => 'FAC001',
            'designation_id' => $designation->id,
            'department_id' => $branch->id,
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // 4. Student
        $student = User::create([
            'name' => 'Alex Student',
            'first_name' => 'Alex',
            'last_name' => 'Student',
            'gender' => 'male',
            'email' => 'student@rvrjc.ac.in',
            'password' => $password,
            'mobile_number' => '9000000004',
            'role' => User::ROLE_STUDENT,
            'roll_number' => 'Y20CS001',
            'course' => 'B.Tech',
            'branch_id' => $branch->id,
            'year' => '4',
            'date_of_birth' => '2002-10-10',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // 5. Unverified Student (for testing)
        User::create([
            'name' => 'Jane Pending',
            'first_name' => 'Jane',
            'last_name' => 'Pending',
            'gender' => 'female',
            'email' => 'pending@rvrjc.ac.in',
            'password' => $password,
            'mobile_number' => '9000000005',
            'role' => User::ROLE_STUDENT,
            'roll_number' => 'Y21CS050',
            'course' => 'B.Tech',
            'branch_id' => $branch->id,
            'year' => '3',
            'date_of_birth' => '2003-01-01',
            'is_verified' => false,
            'email_verified_at' => now(), // Email verified but not hierarchy
        ]);
    }
}
