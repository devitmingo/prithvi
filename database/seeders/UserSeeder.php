<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(User::count() > 0){
            return;
        }

        User::create([
            'user_id' => 'SUP' . date('ymdHis') . rand(100, 999),
            'name' => 'Super Admin',
            'mobile' => '1234567890',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'Superadmin',
            'status' => 1, //Approved,
            'approved_date' => now()
        ]);
    }
}
