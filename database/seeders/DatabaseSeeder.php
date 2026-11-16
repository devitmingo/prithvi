<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\{ 
    StateSeeder,
    CitySeeder,
    UserSeeder,
    FaqSeeder,
    SettingSeeder,
    DesignationSeeder
};

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(StateSeeder::class);
        $this->call(CitySeeder::class);
        $this->call(UserSeeder::class);
        $this->call(FaqSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(DesignationSeeder::class);
    }
}
