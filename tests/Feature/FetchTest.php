<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FetchTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_fetch_api_working_properly(): void
    {
        $apis = [
            '/api/states',
            '/api/cities',
            '/api/designations',
            '/api/terms-and-conditions',
            '/api/privacy-policy',
            '/api/faqs',
            '/api/app-info',
            '/api/active-agents',
            '/api/active-projects',
        ];

        foreach ($apis as $api) {
            $this->getJson($api)->assertStatus(200);
        }
    }
}
