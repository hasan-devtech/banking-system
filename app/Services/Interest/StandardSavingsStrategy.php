<?php

namespace App\Services\Interest;

use App\Models\Account;

class StandardSavingsStrategy implements InterestStrategy
{
    protected float $rate = 0.05; // 5% Annual

    public function calculate(Account $account): float
    {
        // Logic: Calculate monthly portion of annual rate
        // Balance * (Rate / 12 months)
        return $account->balance * ($this->rate / 12);
    }
}
