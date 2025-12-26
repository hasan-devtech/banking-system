<?php

namespace App\Services\Interest;

use App\Models\Account;

class HighYieldStrategy implements InterestStrategy
{
    public function calculate(Account $account): float
    {
        $balance = $account->balance;
        
        // Tiered Logic: 
        // 10% for first 10k, 5% for the rest.
        if ($balance <= 10000) {
            $annualInterest = $balance * 0.10;
        } else {
            $firstTier = 10000 * 0.10;
            $remaining = ($balance - 10000) * 0.05;
            $annualInterest = $firstTier + $remaining;
        }

        return $annualInterest / 12; // Monthly
    }
}