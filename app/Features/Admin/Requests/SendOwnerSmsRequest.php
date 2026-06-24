<?php

namespace App\Features\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates an admin broadcast SMS to group owners.
 */
class SendOwnerSmsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'subscription_status' => ['required', 'string', Rule::in(['all', 'trial', 'active', 'expired', 'suspended'])],
            'body' => ['required', 'string', 'max:1000'],
        ];
    }
}
