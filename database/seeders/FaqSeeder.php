<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(DB::table('faqs')->count() == 0){
            DB::table('faqs')->insert([
                [
                    'sequence' => 1,
                    'question' => 'What is your return policy?',
                    'answer' => 'Our return policy allows returns within 30 days of purchase with a valid receipt.',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'sequence' => 2,
                    'question' => 'How do I track my order?',
                    'answer' => 'You can track your order using the tracking link sent to your email after shipping.',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'sequence' => 3,
                    'question' => 'Do you offer customer support?',
                    'answer' => 'Yes, we offer 24/7 customer support through phone, email, and live chat.',
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
