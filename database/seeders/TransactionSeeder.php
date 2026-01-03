<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = Account::all();
        Transaction::factory(500)->create([
            'from_account_id' => fn() => $accounts->random()->id,
            'to_account_id' => fn() => $accounts->random()->id,
        ]);
    }
}
