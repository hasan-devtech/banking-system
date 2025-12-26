<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {   
        $fromAccount = $transaction->fromAccount;
        $toAccount = $transaction->toAccount;

        if ($fromAccount && $fromAccount->user_id === $user->id) {
            return true;
        }

        if ($toAccount && $toAccount->user_id === $user->id) {
            return true;
        }

        return $user->isAdmin();
    }
}
