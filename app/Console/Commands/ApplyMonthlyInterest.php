<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Models\Transaction;
use App\Services\Interest\InterestCalculatorFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplyMonthlyInterest extends Command
{
    protected $signature = 'interest:apply';
    protected $description = 'Calculate and deposit monthly interest for all eligible accounts';

    public function handle()
    {
        $this->info('Starting Interest Calculation...');

        // Process in chunks to handle thousands of accounts memory-efficiently
        Account::whereNotNull('interest_strategy')
               ->where('status', 'active')
               ->chunk(100, function ($accounts) {
                   
            foreach ($accounts as $account) {
                $strategy = InterestCalculatorFactory::make($account->interest_strategy);
                
                if (!$strategy) continue;

                $amount = $strategy->calculate($account);

                if ($amount > 0) {
                    $this->applyInterest($account, $amount);
                }
            }
        });

        $this->info('Interest calculation complete.');
    }

    private function applyInterest(Account $account, float $amount)
    {
        DB::transaction(function () use ($account, $amount) {
            
            // 1. Create Transaction Record
            Transaction::create([
                'uuid' => (string) Str::uuid(),
                'to_account_id' => $account->id,
                'amount' => $amount,
                'type' => 'interest',
                'status' => 'completed',
                'payment_provider' => 'system',
                'metadata' => ['month' => now()->format('Y-m')]
            ]);

            // 2. Update Balance
            $account->increment('balance', $amount);
            
        });
    }
}