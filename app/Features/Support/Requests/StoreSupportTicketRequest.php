<?php

namespace App\Features\Support\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Store Support Ticket.
 */
class StoreSupportTicketRequest extends FormRequest
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
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
        ];
    }
}
