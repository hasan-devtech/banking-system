<?php

namespace App\Http\Requests\Api\Account;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:savings,checking,loan,investment',
            'currency' => 'nullable|string|size:3',
            'parent_id' => 'nullable|exists:accounts,id',
        ];
    }
}
