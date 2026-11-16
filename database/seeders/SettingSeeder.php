<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Setting::count() == 0) {
            $now = now();

            Setting::insert([
                [
                    'key_name' => 'company_name',
                    'value' => 'TruDeveloper',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key_name' => 'email',
                    'value' => 'admin@truedeveloper.com',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key_name' => 'mobile',
                    'value' => '1234567890',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key_name' => 'address',
                    'value' => 'Sankar Nagar, Raipur, Chhattisgarh, India',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key_name' => 'web_url',
                    'value' => 'https://trudevelopes.itmingo.com/',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key_name' => 'web_version',
                    'value' => '1.0.0',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key_name' => 'app_version',
                    'value' => '1.0.0',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key_name' => 'terms_conditions',
                    'value' => '<p>Your terms and conditions content goes here.</p>',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key_name' => 'privacy_policy',
                    'value' => '<p>Your privacy policy content goes here.</p>',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],

                [
                    'key_name' => 'facebook_url',
                    'value' => 'https://www.facebook.com/',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key_name' => 'instagram_url',
                    'value' => 'https://www.instagram.com/',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'key_name' => 'twitter_url',
                    'value' => 'https://www.twitter.com/',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }
    }
}
