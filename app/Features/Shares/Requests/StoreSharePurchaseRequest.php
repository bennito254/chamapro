<?php

namespace App\Features\Shares\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Store Share Purchase.
 */
class StoreSharePurchaseRequest extends FormRequest
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
            'member_id' => ['required', 'exists:members,id'],
            'shares' => ['nullable', 'integer', 'min:1'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
        ];
    }
}
