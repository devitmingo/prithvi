<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Masters\State;

class StateTest extends TestCase
{
    //use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_state_fetch_successfully(): void
    { 
        // dd(env('APP_NAME'));
        // State::create([
        //     'name' => 'Goa',
        //     'status' => 0,
        // ]);

        $response = $this->getJson('/api/states');
        //         // dd($response['data']);
        $response->assertStatus(200);
        // // $response->assertJsonStructure([
        // //     'status',
        // //     'message',
        // //     'data' => [
        // //         ['label', 'value']
        // //     ],
        // // ]);
        // //$response->assertJsonFragment(['label' => 'Goa']);
        // //$this->assertCount(1, $response->json());
    }
}
