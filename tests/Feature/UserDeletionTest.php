<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;

class UserDeletionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_can_request_account_deletion()
    {
        $mobile = '98765' . rand(10000, 99999);
        $user = User::create([
            'user_id' => 'AGT' . time() . rand(10, 99),
            'name' => 'Delete Test User',
            'mobile' => $mobile,
            'email' => 'deltest' . rand(1000, 9999) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Agent',
            'status' => 1,
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/delete-account-request');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        $freshUser = User::find($user->id);
        $this->assertNotNull($freshUser->deletion_requested_at);

        // Clean up test user
        $freshUser->deleteWithAllRecords();
    }

    public function test_account_deletion_status()
    {
        $mobile = '98765' . rand(10000, 99999);
        $user = User::create([
            'user_id' => 'AGT' . time() . rand(10, 99),
            'name' => 'Status Test User',
            'mobile' => $mobile,
            'email' => 'status' . rand(1000, 9999) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Agent',
            'status' => 1,
            'deletion_requested_at' => Carbon::now()->subDays(10),
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/delete-account-status');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'is_deletion_pending' => true,
                    'days_remaining' => 80,
                ]
            ]);

        // Clean up test user
        $user->deleteWithAllRecords();
    }

    public function test_logging_in_within_90_days_cancels_deletion_request()
    {
        $mobile = '98765' . rand(10000, 99999);
        $user = User::create([
            'user_id' => 'AGT' . time() . rand(10, 99),
            'name' => 'Login Restore User',
            'mobile' => $mobile,
            'email' => 'restore' . rand(1000, 9999) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Agent',
            'status' => 1,
            'deletion_requested_at' => Carbon::now()->subDays(30),
        ]);

        $response = $this->postJson('/api/login', [
            'mobile' => $mobile,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'deletion_cancelled' => true,
                ]
            ]);

        $freshUser = User::find($user->id);
        $this->assertNull($freshUser->deletion_requested_at);

        // Clean up test user
        $freshUser->deleteWithAllRecords();
    }

    public function test_manual_cancel_account_deletion()
    {
        $mobile = '98765' . rand(10000, 99999);
        $user = User::create([
            'user_id' => 'AGT' . time() . rand(10, 99),
            'name' => 'Cancel Test User',
            'mobile' => $mobile,
            'email' => 'cancel' . rand(1000, 9999) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Agent',
            'status' => 1,
            'deletion_requested_at' => Carbon::now()->subDays(5),
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/cancel-delete-account');

        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
            ]);

        $freshUser = User::find($user->id);
        $this->assertNull($freshUser->deletion_requested_at);

        // Clean up test user
        $freshUser->deleteWithAllRecords();
    }

    public function test_scheduled_command_purges_users_past_90_days()
    {
        $mobile = '98765' . rand(10000, 99999);
        $user = User::create([
            'user_id' => 'AGT' . time() . rand(10, 99),
            'name' => 'Expired Test User',
            'mobile' => $mobile,
            'email' => 'expired' . rand(1000, 9999) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Agent',
            'status' => 1,
            'deletion_requested_at' => Carbon::now()->subDays(95),
        ]);

        $userId = $user->id;

        Artisan::call('users:delete-expired');

        $this->assertNull(User::find($userId));
    }

    public function test_login_after_90_days_purges_user_and_fails()
    {
        $mobile = '98765' . rand(10000, 99999);
        $user = User::create([
            'user_id' => 'AGT' . time() . rand(10, 99),
            'name' => 'Expired Login User',
            'mobile' => $mobile,
            'email' => 'expiredlogin' . rand(1000, 9999) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Agent',
            'status' => 1,
            'deletion_requested_at' => Carbon::now()->subDays(92),
        ]);

        $userId = $user->id;

        $response = $this->postJson('/api/login', [
            'mobile' => $mobile,
            'password' => 'password123',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'status' => false,
                'error' => 'Account permanently deleted',
            ]);

        $this->assertNull(User::find($userId));
    }
}
