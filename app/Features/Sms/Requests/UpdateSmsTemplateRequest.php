<?php

namespace App\Features\Sms\Requests;

use App\Features\Sms\Models\SmsTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form request validation for Update Sms Template.
 */
class UpdateSmsTemplateRequest extends FormRequest
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
        /** @var SmsTemplate $template */
        $template = $this->route('sms_template');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sms_templates', 'name')
                    ->where(fn ($query) => $query->where('group_id', $this->user()?->group_id))
                    ->ignore($template->id),
            ],
            'body' => ['required', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }
}
