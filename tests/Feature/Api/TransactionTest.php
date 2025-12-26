<?php

use App\Models\User;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can deposit money', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id, 'balance' => 0]);

    $response = $this->actingAs($user)->postJson('/api/transactions/deposit', [
        'account_id' => $account->id,
        'amount' => 100,
        'provider' => 'stripe'
    ]);

    $response->assertStatus(200);
    expect($account->fresh()->balance)->toEqual(100.0);
});

test('user can transfer money to another account', function () {
    $user1 = User::factory()->create();
    $fromAccount = Account::factory()->create(['user_id' => $user1->id, 'balance' => 500]);
    
    $user2 = User::factory()->create();
    $toAccount = Account::factory()->create(['user_id' => $user2->id, 'balance' => 0]);

    $response = $this->actingAs($user1)->postJson('/api/transactions/transfer', [
        'from_account_id' => $fromAccount->id,
        'to_account_number' => $toAccount->account_number,
        'amount' => 100,
    ]);

    $response->assertStatus(200);

    // Assert balances adjusted
    expect($fromAccount->fresh()->balance)->toEqual(400.0);
    expect($toAccount->fresh()->balance)->toEqual(100.0);
});

test('huge transfer triggers approval', function () {
    $user = User::factory()->create();
    $fromAccount = Account::factory()->create(['user_id' => $user->id, 'balance' => 20000]);
    $toAccount = Account::factory()->create(['balance' => 0]);

    // Assume threshold is 10000
    $response = $this->actingAs($user)->postJson('/api/transactions/transfer', [
        'from_account_id' => $fromAccount->id,
        'to_account_number' => $toAccount->account_number,
        'amount' => 15000,
    ]);

    $response->assertStatus(202)
        ->assertJsonPath('data.status', 'pending_approval');

    // Balance should NOT change yet
    expect($fromAccount->fresh()->balance)->toEqual(20000.0);
});

test('transfer fails if insufficient funds', function () {
    $user = User::factory()->create();
    $fromAccount = Account::factory()->create(['user_id' => $user->id, 'balance' => 50]);
    $toAccount = Account::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/transactions/transfer', [
        'from_account_id' => $fromAccount->id,
        'to_account_number' => $toAccount->account_number,
        'amount' => 100,
    ]);

    $response->assertStatus(400); 
});
