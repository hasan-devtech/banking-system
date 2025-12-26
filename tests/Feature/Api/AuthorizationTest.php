<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_another_users_account()
    {
        $userA = User::factory()->create();
        $accountA = Account::factory()->create(['user_id' => $userA->id]);

        $userB = User::factory()->create();

        $response = $this->actingAs($userB)->getJson("/api/accounts/{$accountA->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_view_any_users_account()
    {
        $userA = User::factory()->create();
        $accountA = Account::factory()->create(['user_id' => $userA->id]);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson("/api/accounts/{$accountA->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_deposit_to_another_users_account()
    {
        $userA = User::factory()->create();
        $accountA = Account::factory()->create(['user_id' => $userA->id, 'balance' => 100]);

        $userB = User::factory()->create();

        $response = $this->actingAs($userB)->postJson('/api/transactions/deposit', [
            'account_id' => $accountA->id,
            'amount' => 50
        ]);

        $response->assertStatus(403);
    }

    public function test_frozen_account_cannot_transfer()
    {
        $user = User::factory()->create();
        $sourceAccount = Account::factory()->create([
            'user_id' => $user->id, 
            'balance' => 100, 
            'status' => 'frozen',
            'account_number' => '1234567890'
        ]);
        
        $targetAccount = Account::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/transactions/transfer', [
            'from_account_id' => $sourceAccount->id,
            'to_account_number' => $targetAccount->account_number,
            'amount' => 50
        ]);

        // Should be forbidden because frozen state disallows withdrawal/transfer
        $response->assertStatus(403);
    }

    public function test_frozen_account_cannot_request_change()
    {
        $user = User::factory()->create();
        $account = Account::factory()->create([
            'user_id' => $user->id, 
            'balance' => 100, 
            'status' => 'frozen'
        ]);

        $response = $this->actingAs($user)->postJson("/api/accounts/{$account->id}/change-request", [
            'changes' => ['type' => 'checking']
        ]);

        // Should be forbidden because frozen state disallows modification
        $response->assertStatus(403);
    }
}
