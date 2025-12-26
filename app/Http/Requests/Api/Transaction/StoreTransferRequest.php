<?php

namespace App\Http\Requests\Api\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
             'from_account_id' => 'required|exists:accounts,id',
             'to_account_number' => 'required|exists:accounts,account_number',
             'amount' => 'required|numeric|min:0.01',
        ];
    }
}
