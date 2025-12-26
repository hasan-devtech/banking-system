<?php

namespace App\Services\PaymentGateways;

use Exception;

class StripeAdapter implements PaymentGatewayInterface
{
    // public function __construct(StripeClient $stripe) { ... }

    public function charge(float $amount, string $currency, array $metadata = []): string
    {
        // 1. Convert amount to cents (Stripe logic)
        $cents = $amount * 100;

        // 2. Simulate API Call
        // $response = $this->stripe->charges->create([...]);
        
        // Mock Response
        $success = true; // Simulate success
        
        if (!$success) {
            throw new Exception("Stripe Charge Failed");
        }

        // Return the provider's transaction ID
        return "stripe_ch_" . uniqid(); 
    }

    public function refund(string $transactionId, float $amount): bool
    {
        // Simulate Refund API logic
        return true;
    }
}