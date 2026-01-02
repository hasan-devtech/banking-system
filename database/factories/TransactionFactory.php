<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'from_account_id' => Account::factory(),
            'to_account_id' => Account::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'type' => $this->faker->randomElement(['deposit', 'withdrawal', 'transfer']),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'failed_waiting_for_funds', 'pending_approval']),
            'payment_provider' => $this->faker->randomElement(['stripe', 'paypal', 'manual']),
            'provider_transaction_id' => $this->faker->uuid(),
            'metadata' => [
                'notes' => $this->faker->sentence(),
                'ip' => $this->faker->ipv4(),
            ],
        ];
    }

    /**
     * State for a failed transaction waiting for funds.
     */
    public function failedWaitingForFunds(): static
    {
        return $this->state(fn() => ['status' => 'failed_waiting_for_funds']);
    }

    /**
     * State for pending approval transactions.
     */
    public function pendingApproval(): static
    {
        return $this->state(fn() => ['status' => 'pending_approval']);
    }
}
