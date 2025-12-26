<?php

namespace App\Listeners;

use App\Events\TransactionCreated;
use Illuminate\Support\Facades\Log;

class CheckHighValueThreshold
{
    public function handle(TransactionCreated $event)
    {
        $tx = $event->transaction;
        if ($tx->amount > 10000) {
            $this->sendNotification($tx);
        }
    }

    private function sendNotification($tx)
    {
        //make notification
        
        app(\App\Services\AuditLogger::class)->log(
            'transaction.high_value_alert', 
            $tx, 
            ['amount' => $tx->amount, 'currency' => 'USD'] // Assuming USD for now
        );
    }
}