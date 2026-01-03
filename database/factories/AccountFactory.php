<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition()
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'account_number' => $this->faker->unique()->bankAccountNumber,
            'type' => $this->faker->randomElement(['savings', 'checking']),
            'status' => 'active',
            'balance' => 100000.00,
            'currency' => 'USD',
        ];
    }
}
