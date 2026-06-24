<?php

namespace App\Features\Loans\Requests;

use App\Enums\LoanApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request validation for Transition Loan Application.
 */
class TransitionLoanApplicationRequest extends FormRequest
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
            'status' => ['required', Rule::enum(LoanApplicationStatus::class)],
            'review_notes' => ['nullable', 'string'],
        ];
    }
}
