<?php

namespace App\Features\Contributions\Requests;

use App\Enums\AmountType;
use App\Enums\ContributionFrequency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request validation for Update Contribution Type.
 */
class UpdateContributionTypeRequest extends FormRequest
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
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'amount_type' => ['required', Rule::enum(AmountType::class)],
            'frequency' => ['required', Rule::enum(ContributionFrequency::class)],
            'status' => ['required', 'string', 'in:active,inactive'],
            'save_to_bank' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'save_to_bank' => $this->boolean('save_to_bank'),
        ]);
    }
}
