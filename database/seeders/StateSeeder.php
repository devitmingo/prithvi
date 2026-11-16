<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(DB::table('states')->count() > 0){
            return;
        }

        $states = [
            ['name' => 'Andhra Pradesh', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Arunachal Pradesh', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Assam', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bihar', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Chhattisgarh', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Goa', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gujarat', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Haryana', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Himachal Pradesh', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jharkhand', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Karnataka', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kerala', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Madhya Pradesh', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maharashtra', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Manipur', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Meghalaya', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mizoram', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nagaland', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Odisha', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Punjab', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rajasthan', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sikkim', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tamil Nadu', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Telangana', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tripura', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Uttar Pradesh', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Uttarakhand', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'West Bengal', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Andaman and Nicobar Islands', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Chandigarh', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Dadra and Nagar Haveli and Daman and Diu', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lakshadweep', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Delhi', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Puducherry', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ladakh', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jammu and Kashmir', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        // Insert all states into the states table
        DB::table('states')->insert($states);
    }
}
