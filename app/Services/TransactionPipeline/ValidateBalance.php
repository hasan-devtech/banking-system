<?php

namespace App\Services\TransactionPipeline;

use Closure;
use Exception;

class ValidateBalance
{
    public function handle($transaction, Closure $next)
    {
        if ($transaction->type === 'deposit') {
            return $next($transaction);
        }

        // This is now INSTANT because we used setRelation() above
        $account = $transaction->fromAccount;

        // Fast state check
        if (!$account->state->canWithdraw()) {
            throw new Exception("Account is {$account->status} and cannot withdraw.");
        }

        // Atomic balance check (already locked by lockForUpdate in the Service)
        if ($account->balance < $transaction->amount) {
            throw new Exception("Insufficient funds.");
        }

        return $next($transaction);
    }
}