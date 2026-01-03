<?php

namespace App\Jobs;

use App\Models\OutboxEvent;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessOutboxEvents implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        OutboxEvent::whereNull('processed_at')
            ->limit(50)
            ->get()
            ->each(function ($outbox) {
                $transaction = Transaction::find(
                    $outbox->payload['transaction_id']
                );
                if (!$transaction) {
                    return;
                }
                logger($outbox);
                event(new $outbox->type($transaction));
                $outbox->update([
                    'processed_at' => now()
                ]);
            });
    }
}
