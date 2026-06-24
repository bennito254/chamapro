<?php

namespace App\Features\Banking\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Store Bank Transaction.
 */
class StoreBankTransactionRequest extends FormRequest
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
            'type' => ['required', 'string', 'in:receive,pay,transfer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'destination_bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
        ];
    }
}
