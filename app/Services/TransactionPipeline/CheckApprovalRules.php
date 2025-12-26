<?php

namespace App\Services\TransactionPipeline;

use Closure;

class CheckApprovalRules
{
    public function handle($transaction, Closure $next)
    {
        // Rule: Transactions over 1000 require approval
        $approvalThreshold = 1000.00;

        if ($transaction->amount > $approvalThreshold) {
            $transaction->status = 'pending_approval';
            $transaction->save();

            // We STOP the chain here. The transaction is saved but not executed.
            // Returning the transaction object allows the controller to see the status.
            return $transaction;
        }

        // If small amount, auto-approve
        $transaction->status = 'processing';
        
        return $next($transaction);
    }
}