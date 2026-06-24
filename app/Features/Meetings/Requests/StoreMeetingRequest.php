<?php

namespace App\Features\Meetings\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Store Meeting.
 */
class StoreMeetingRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'time' => ['nullable', 'string', 'max:20'],
            'venue' => ['nullable', 'string', 'max:255'],
            'agenda' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:scheduled,completed,cancelled'],
        ];
    }
}
