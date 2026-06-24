<?php

namespace App\Features\Sms\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request validation for Store Sms Template.
 */
class StoreSmsTemplateRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sms_templates', 'name')->where(
                    fn ($query) => $query->where('group_id', $this->user()?->group_id),
                ),
            ],
            'body' => ['required', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}
