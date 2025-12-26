<?php

namespace App\States\Account;

class ActiveState implements AccountState
{
    public function canDeposit(): bool { return true; }
    public function canWithdraw(): bool { return true; }
    public function canTransferIn(): bool { return true; }
    public function canTransferOut(): bool { return true; }
    public function canBeModified(): bool { return true; }
}