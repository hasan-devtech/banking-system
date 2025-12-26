<?php

namespace App\Services;

use App\Events\TransactionCreated;
use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Pipeline\Pipeline;
use App\Services\TransactionPipeline\ValidateBalance;
use App\Services\TransactionPipeline\CheckApprovalRules;
use App\Services\TransactionPipeline\ExecutePayment;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function createTransfer(Account $from, Account $to, float $amount)
    {
        // 1. Create the Transaction Record (Initial State)
        $transaction = Transaction::create([
            'from_account_id' => $from->id,
            'to_account_id' => $to->id,
            'amount' => $amount,
            'type' => 'transfer',
            'status' => 'pending_approval', // Default
        ]);

        TransactionCreated::dispatch($transaction);

        // 2. Run the Pipeline (Chain of Responsibility)
        return app(Pipeline::class)
            ->send($transaction)
            ->through([
                ValidateBalance::class,
                CheckApprovalRules::class,
                ExecutePayment::class,
            ])
            ->thenReturn();
    }

    public function deposit(Account $to, float $amount)
    {
        $transaction = Transaction::create([
            'to_account_id' => $to->id,
            'amount' => $amount,
            'type' => 'deposit',
            'status' => 'processing',
        ]);

        return app(Pipeline::class)
            ->send($transaction)
            ->through([
                // ValidateBalance is skipped logic internally for deposits
                ValidateBalance::class, 
                // Deposits usually auto-approve, but you can keep rules if needed
                ExecutePayment::class,
            ])
            ->thenReturn();
    }


    /**
     * Admin approves a pending transaction.
     */
    public function approveTransaction(Transaction $transaction)
    {
        if ($transaction->status !== 'pending_approval') {
            throw new \Exception("Transaction is not pending approval.");
        }

        // 1. Re-validate Balance (Crucial Step!)
        // The user might have spent funds while waiting for admin.
        if ($transaction->fromAccount) {
             if (!$transaction->fromAccount->state->canWithdraw()) {
                 $transaction->update(['status' => 'failed']);
                 throw new \Exception("Account is now frozen/suspended.");
             }
             
             if ($transaction->fromAccount->balance < $transaction->amount) {
                 $transaction->update(['status' => 'failed']);
                 throw new \Exception("Insufficient funds (Balance changed during wait).");
             }
        }

        // 2. Execute the Payment
        // We can manually call the ExecutePayment handler or logic here.
        // Since we are bypassing the pipeline (we already validated rules), we call logic directly.
        
        DB::transaction(function () use ($transaction) {
            if ($transaction->type === 'transfer') {
                // For transfers, BOTH must exist. If one is missing, throw an error to rollback.
                if (!$transaction->fromAccount || !$transaction->toAccount) {
                    throw new \Exception("Data Corruption: Transfer missing source or target.");
                }
            }

            if ($transaction->fromAccount) {
                $transaction->fromAccount->decrement('balance', $transaction->amount);
            }

            if ($transaction->toAccount) {
                $transaction->toAccount->increment('balance', $transaction->amount);
            }

            $transaction->update(['status' => 'completed']);
        });

        return $transaction;
    }

    /**
     * Admin rejects a transaction.
     */
    public function rejectTransaction(Transaction $transaction)
    {
        if ($transaction->status !== 'pending_approval') {
            throw new \Exception("Transaction is not pending approval.");
        }

        $transaction->update(['status' => 'rejected']);
        return $transaction;
    }
}