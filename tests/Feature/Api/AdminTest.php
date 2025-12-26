<?php

use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\ChangeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can approve transaction', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    // Create a pending transaction
    $from = Account::factory()->create(['balance' => 1000]);
    $to = Account::factory()->create(['balance' => 0]);
    
    $transaction = Transaction::create([
        'from_account_id' => $from->id,
        'to_account_id' => $to->id,
        'type' => 'transfer',
        'amount' => 500,
        'status' => 'pending_approval'
    ]);

    $response = $this->actingAs($admin)->postJson("/api/admin/transactions/{$transaction->id}/approve");

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'completed');

    expect($from->fresh()->balance)->toEqual(500.0);
    expect($to->fresh()->balance)->toEqual(500.0);
});

test('admin can reject transaction', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $transaction = Transaction::create([
        'type' => 'deposit', // Simplified
        'amount' => 500,
        'status' => 'pending_approval'
    ]);

    $response = $this->actingAs($admin)->postJson("/api/admin/transactions/{$transaction->id}/reject");

    $response->assertStatus(200)
        ->assertJsonPath('data.status', 'rejected');
});

test('admin can approve change request', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $account = Account::factory()->create(['type' => 'savings']);
    
    $change = ChangeRequest::create([
        'account_id' => $account->id,
        'requested_changes' => ['type' => 'checking'],
        'status' => 'pending',
        'requester_id' => $admin->id 
    ]);

    $response = $this->actingAs($admin)->postJson("/api/admin/change-requests/{$change->id}/approve");

    $response->assertStatus(200);
    expect($account->fresh()->type)->toBe('checking'); 
});
