<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(DB::table('cities')->count() > 0){
            return;
        }

        $cities = [
            ['state_id' => 5, 'name' => 'Ambikapur', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Balod', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Balodabazar', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Bacheli', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Bemetara', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Bilaspur', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Bijapur', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Chhura', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Chirmiri', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Dantewada', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Dongargarh', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Durg', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Jagdalpur', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Jashpur', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Kanker', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Korba', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Koriya', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Mahasamund', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Malkangiri', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Mungeli', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Naya Raipur', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Pathalgaon', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Raigarh', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Raipur', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Rajim', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Rajnandgaon', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Sakti', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Surajpur', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
            ['state_id' => 5, 'name' => 'Surguja', 'status' => true, 'created_at' => now(), 'updated_at' => now()],
        ];
        // Insert all cities (districts) into the cities table
        DB::table('cities')->insert($cities);
    }
}
