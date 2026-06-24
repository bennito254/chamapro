<?php

namespace App\Features\Loans\Requests;

use App\Enums\InterestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request validation for Store Loan Product.
 */
class StoreLoanProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_amount' => ['required', 'numeric', 'min:0'],
            'max_multiplier' => ['nullable', 'numeric', 'min:0'],
            'interest_type' => ['required', Rule::enum(InterestType::class)],
            'interest_value' => ['required', 'numeric', 'min:0'],
            'repayment_period' => ['required', 'integer', 'min:1'],
            'grace_period' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}
