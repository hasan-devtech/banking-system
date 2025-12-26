<?php

namespace App\Services\PaymentGateways;

interface PaymentGatewayInterface
{
    /**
     * Charge a specific amount.
     * @return string The external transaction ID 
     */
    public function charge(float $amount, string $currency, array $metadata = []): string;

    /**
     * Refund a transaction.
     */
    public function refund(string $transactionId, float $amount): bool;
}