<?php

namespace App\Features\Loans\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Update Loan Application.
 */
class UpdateLoanApplicationRequest extends FormRequest
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
            'loan_product_id' => ['required', 'exists:loan_products,id'],
            'requested_amount' => ['required', 'numeric', 'min:1'],
            'purpose' => ['required', 'string'],
        ];
    }
}
