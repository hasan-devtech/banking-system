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
        if ($transaction->type === 'deposit') {
            $externalId = $this->gateway->charge(
                $transaction->amount, 
                'USD', 
                ['tx_id' => $transaction->id]
            );
            $transaction->provider_transaction_id = $externalId;
        }

        
        if ($transaction->from_account_id) {
            DB::table('accounts')
                ->where('id', $transaction->from_account_id)
                ->decrement('balance', $transaction->amount);
        }

        if ($transaction->to_account_id) {
            DB::table('accounts')
                ->where('id', $transaction->to_account_id)
                ->increment('balance', $transaction->amount);
        }
        $transaction->update(['status' => 'completed']);

        return $next($transaction);
    }
}