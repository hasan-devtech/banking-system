<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AccountPolicy
{

    public function view(User $user, Account $account): bool
    {
        return $user->id === $account->user_id || $user->isAdmin();
    }


    public function create(User $user): bool
    {
        // Any authenticated user can create an account
        return true;
    }


    public function deposit(User $user, Account $account): bool
    {
        // Users can only deposit into their own accounts AND if state allows
        return $user->id === $account->user_id && $account->state->canDeposit();
    }


    public function withdraw(User $user, Account $account): bool
    {
        // Users can only withdraw from their own accounts AND if state allows
        return $user->id === $account->user_id && $account->state->canWithdraw();
    }

    public function requestChange(User $user, Account $account): bool
    {
        return $user->id === $account->user_id && $account->state->canBeModified();
    }
}
