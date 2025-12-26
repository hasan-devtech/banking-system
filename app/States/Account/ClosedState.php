<?php

namespace App\States\Account;

class ClosedState implements AccountState
{
    public function canDeposit(): bool { return false; }
    public function canWithdraw(): bool { return false; }
    public function canTransferIn(): bool { return false; }
    public function canTransferOut(): bool { return false; }
    public function canBeModified(): bool { return false; }
}