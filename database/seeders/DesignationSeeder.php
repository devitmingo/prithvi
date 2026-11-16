<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Masters\Designation;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(Designation::count() == 0){
            $designations = [
                // Management
                ['name' => 'Chairman'],
                ['name' => 'Director'],
                ['name' => 'Principal'],
                ['name' => 'Vice Principal'],
                ['name' => 'Administrator'],
                ['name' => 'Manager'],

                // Education Department
                ['name' => 'Head of Department (HOD)'],
                ['name' => 'Senior Teacher'],
                ['name' => 'Assistant Teacher'],
                ['name' => 'Lecturer'],
                ['name' => 'Professor'],
                ['name' => 'Assistant Professor'],
                ['name' => 'Lab Assistant'],
                ['name' => 'Librarian'],
                ['name' => 'Clerk'],

                // Medical Department
                ['name' => 'Chief Medical Officer'],
                ['name' => 'Doctor'],
                ['name' => 'Surgeon'],
                ['name' => 'Consultant'],
                ['name' => 'Nurse'],
                ['name' => 'Pharmacist'],
                ['name' => 'Lab Technician'],
                ['name' => 'Ward Boy'],
                ['name' => 'Receptionist'],

                // General Staff
                ['name' => 'Accountant'],
                ['name' => 'Office Assistant'],
                ['name' => 'Driver'],
                ['name' => 'Peon'],
                ['name' => 'Security Guard'],
                ['name' => 'Cleaner'],
                ['name' => 'Intern / Trainee'],
            ];

            foreach ($designations as $designation) {
                Designation::create($designation);
            }
        }
    }
}
