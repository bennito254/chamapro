<?php

namespace App\Features\Fines\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Update Fine Type.
 */
class UpdateFineTypeRequest extends FormRequest
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
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}
