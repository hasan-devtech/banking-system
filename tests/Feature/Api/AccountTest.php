<?php

use App\Models\User;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can create an account', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->postJson('/api/accounts', [
        'type' => 'savings',
        'currency' => 'USD',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['message', 'data' => ['id', 'account_number']]);
        
    $this->assertDatabaseHas('accounts', [
        'user_id' => $user->id,
        'type' => 'savings'
    ]);
});

test('user can list their accounts', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/api/accounts');

    $response->assertStatus(200);
});

test('user cannot view others account', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user2->id]);

    $response = $this->actingAs($user1)->getJson("/api/accounts/{$account->id}");

    $response->assertStatus(403);
});

test('user can request account changes', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson("/api/accounts/{$account->id}/change-request", [
        'changes' => ['type' => 'checking']
    ]);

    $response->assertStatus(202)
        ->assertJsonStructure(['message', 'data' => ['id']]); // data contains change request

    $this->assertDatabaseHas('change_requests', [
        'account_id' => $account->id,
        'status' => 'pending'
    ]);
});
