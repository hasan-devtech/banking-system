<?php

namespace App\Services\TransactionPipeline;

use Closure;
use App\Services\PaymentGateways\PaymentGatewayInterface;
use Illuminate\Support\Facades\DB;

class ExecutePayment
{
    protected $gateway;

    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function handle($transaction, Closure $next)
    {
        // 1. Call External Gateway (Adapter Pattern)
        // Only if it involves external money (like deposit/withdrawal)
        // Internal transfers might skip this or use LocalBankAdapter
        
        if ($transaction->type === 'deposit') {
             $externalId = $this->gateway->charge(
                 $transaction->amount, 
                 'USD', 
                 ['tx_id' => $transaction->id]
             );
             $transaction->provider_transaction_id = $externalId;
        }

        // 2. Update Database Balances (Atomic Transaction)
        DB::transaction(function () use ($transaction) {
            
            // Lock rows for concurrency safety
            // In high volume, use optimistic locking, but pessimistic is safer for banking
            
            if ($transaction->fromAccount) {
                $transaction->fromAccount->decrement('balance', $transaction->amount);
            }

            if ($transaction->toAccount) {
                $transaction->toAccount->increment('balance', $transaction->amount);
            }

            $transaction->status = 'completed';
            $transaction->save();
        });

        return $next($transaction);
    }
}