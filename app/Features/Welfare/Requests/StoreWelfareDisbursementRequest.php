<?php

namespace App\Features\Welfare\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Store Welfare Disbursement.
 */
class StoreWelfareDisbursementRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string'],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
