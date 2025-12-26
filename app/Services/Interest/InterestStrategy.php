<?php

namespace App\Services\Interest;

use App\Models\Account;

interface InterestStrategy
{
    /**
     * Calculate interest amount based on balance.
     * Returns the calculated amount (does not apply it).
     */
    public function calculate(Account $account): float;
}

