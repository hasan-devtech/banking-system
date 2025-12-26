<?php

namespace App\Services\TransactionPipeline;

use Closure;
use Exception;

class ValidateBalance
{
    public function handle($transaction, Closure $next)
    {
        // Skip check for Deposits (no balance needed to receive money)
        if ($transaction->type === 'deposit') {
            return $next($transaction);
        }

        $account = $transaction->fromAccount;

        // Use the State Pattern we built earlier!
        if (!$account->state->canWithdraw()) {
            throw new Exception("Account is {$account->status} and cannot withdraw funds.");
        }

        // Check funds
        if ($account->balance < $transaction->amount) {
            throw new Exception("Insufficient funds.");
        }

        return $next($transaction);
    }
}