<?php

namespace App\Features\Banking\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Store Bank Account.
 */
class StoreBankAccountRequest extends FormRequest
{
    /**
     * Authorize.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:100'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}
