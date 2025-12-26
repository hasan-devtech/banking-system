<?php

namespace App\Services\Interest;

use Exception;

class InterestCalculatorFactory
{
    public static function make(?string $strategyName): ?InterestStrategy
    {
        return match ($strategyName) {
            'standard_savings' => new StandardSavingsStrategy(),
            'high_yield' => new HighYieldStrategy(),
            default => null, // No interest for checking/loans via this specific job
        };
    }
}