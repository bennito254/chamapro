<?php

namespace App\Features\Members\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Import Members.
 */
class ImportMembersRequest extends FormRequest
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
            'members' => ['required', 'array', 'min:1'],
            'members.*.membership_number' => ['required', 'string', 'max:50'],
            'members.*.full_name' => ['required', 'string', 'max:255'],
            'members.*.phone_number' => ['nullable', 'string', 'max:50'],
            'members.*.email' => ['nullable', 'email', 'max:255'],
            'members.*.date_joined' => ['required', 'date'],
        ];
    }
}
