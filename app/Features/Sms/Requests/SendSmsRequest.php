<?php

namespace App\Features\Sms\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Send Sms.
 */
class SendSmsRequest extends FormRequest
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
            'sms_template_id' => ['required', 'integer', 'exists:sms_templates,id'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:members,id'],
        ];
    }
}
