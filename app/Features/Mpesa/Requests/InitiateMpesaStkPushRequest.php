<?php

namespace App\Features\Mpesa\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Initiate Mpesa Stk Push.
 */
class InitiateMpesaStkPushRequest extends FormRequest
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
            'phone_number' => ['required', 'string', 'max:20'],
            'amount' => ['required', 'numeric', 'min:1'],
            'member_id' => ['nullable', 'exists:members,id'],
            'reference' => ['nullable', 'string', 'max:255'],
            'payable_type' => ['nullable', 'string', 'max:255'],
            'payable_id' => ['nullable', 'integer'],
        ];
    }
}
