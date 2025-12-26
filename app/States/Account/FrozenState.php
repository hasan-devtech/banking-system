<?php

namespace App\States\Account;

class FrozenState implements AccountState
{
    public function canDeposit(): bool { return true; }
    
    // Money cannot leave the account
    public function canWithdraw(): bool { return false; }
    public function canTransferOut(): bool { return false; }
    
    // Usually, frozen accounts can still receive transfers
    public function canTransferIn(): bool { return true; }
    
    // Metadata cannot be changed while frozen
    public function canBeModified(): bool { return false; }
}