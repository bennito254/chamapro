<?php

namespace App\Features\Sms\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Preview Sms.
 */
class PreviewSmsRequest extends FormRequest
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
            'member_id' => ['required', 'integer', 'exists:members,id'],
        ];
    }
}
