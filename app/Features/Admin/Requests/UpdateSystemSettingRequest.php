<?php

namespace App\Features\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Update System Setting.
 */
class UpdateSystemSettingRequest extends FormRequest
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
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable'],
        ];
    }
}
