<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Exception;

class AccountService
{
    /**
     * Create a new account (Parent or Child).
     */
    public function createAccount(User $user, array $data): Account
    {
        // 1. Hierarchy Validation (Composite Rule)
        if (isset($data['parent_id'])) {
            $parent = Account::find($data['parent_id']);
            
            if (!$parent) {
                throw new InvalidArgumentException("Parent account not found.");
            }
            
            if ($parent->user_id !== $user->id) {
                throw new Exception("Unauthorized: Cannot link to a parent account you do not own.");
            }
            
            if ($parent->currency !== ($data['currency'] ?? 'USD')) {
               throw new Exception("Child account currency must match parent.");
            }
        }

        // 2. Generate Unique Account Number
        $accountNumber = $this->generateAccountNumber();

        // 3. Determine Default Interest Strategy
        $strategy = $this->getDefaultStrategy($data['type']);

        // 4. Create Account
        return Account::create([
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'account_number' => $accountNumber,
            'type' => $data['type'],
            'currency' => $data['currency'] ?? 'USD',
            'status' => 'active', // Default state
            'balance' => 0.00,
            'interest_strategy' => $strategy,
        ]);
    }

    /**
     * Get the full hierarchy tree for a user.
     * Implements the retrieval side of the Composite Pattern.
     */
    public function getUserHierarchy(User $user)
    {
        // Get only root accounts (parents) and eager load their children recursively
        return Account::where('user_id', $user->id)
            ->whereNull('parent_id')
            ->with('allChildren') 
            ->get();
    }

    /**
     * Helper to generate a random 10-digit number.
     */
    private function generateAccountNumber(): string
    {
        do {
            $number = mt_rand(1000000000, 9999999999);
        } while (Account::where('account_number', $number)->exists());

        return (string) $number;
    }

    /**
     * Map account types to default strategies.
     */
    private function getDefaultStrategy(string $type): ?string
    {
        return match ($type) {
            'savings' => 'standard_compound',
            'investment' => 'high_yield',
            'loan' => 'simple_interest',
            default => null, // Checking accounts might have no interest
        };
    }
}