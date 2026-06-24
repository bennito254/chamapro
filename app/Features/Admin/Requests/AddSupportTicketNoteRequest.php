<?php

namespace App\Features\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Add Support Ticket Note.
 */
class AddSupportTicketNoteRequest extends FormRequest
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
            'body' => ['required', 'string'],
            'is_internal' => ['boolean'],
        ];
    }
}
