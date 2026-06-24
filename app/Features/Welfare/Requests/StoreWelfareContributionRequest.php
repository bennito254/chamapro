<?php

namespace App\Features\Welfare\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Store Welfare Contribution.
 */
class StoreWelfareContributionRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
