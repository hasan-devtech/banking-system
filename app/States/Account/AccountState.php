<?php

namespace App\States\Account;

interface AccountState
{
    public function canDeposit(): bool;
    public function canWithdraw(): bool;
    public function canTransferIn(): bool; // receiving money
    public function canTransferOut(): bool; // sending money
    public function canBeModified(): bool; 
}