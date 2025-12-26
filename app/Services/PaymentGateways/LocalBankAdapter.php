<?php

namespace App\Services\PaymentGateways;


class LocalBankAdapter implements PaymentGatewayInterface
{
    public function charge(float $amount, string $currency, array $metadata = []): string
    {
        // Local bank might not need cent conversion
        // Local bank might require a specific 'routing_number' in metadata
        if (!isset($metadata['routing_number'])) {
            // In a real scenario, you might throw an error or use a default
        }

        // Simulate successful internal hold
        return "local_tx_" . uniqid();
    }

    public function refund(string $transactionId, float $amount): bool
    {
        return true;
    }
}