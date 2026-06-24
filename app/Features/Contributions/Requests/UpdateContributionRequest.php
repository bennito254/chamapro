<?php

namespace App\Features\Contributions\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Update Contribution.
 */
class UpdateContributionRequest extends FormRequest
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
            'contribution_type_id' => ['required', 'exists:contribution_types,id'],
            'contribution_channel_id' => ['required', 'exists:contribution_channels,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
