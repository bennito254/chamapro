<?php

namespace App\Features\Fines\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Update Fine.
 */
class UpdateFineRequest extends FormRequest
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
            'fine_type_id' => ['required', 'exists:fine_types,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'date' => ['required', 'date'],
        ];
    }
}
