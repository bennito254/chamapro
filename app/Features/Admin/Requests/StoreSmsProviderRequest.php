<?php

namespace App\Features\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Store Sms Provider.
 */
class StoreSmsProviderRequest extends FormRequest
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
            'driver' => ['required', 'string', 'max:255'],
            'credentials' => ['required', 'array'],
            'is_default' => ['boolean'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('credentials') && is_string($this->input('credentials'))) {
            $decoded = json_decode($this->input('credentials'), true);

            $this->merge([
                'credentials' => is_array($decoded) ? $decoded : [],
            ]);
        }
    }
}
