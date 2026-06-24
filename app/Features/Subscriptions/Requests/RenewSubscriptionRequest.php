<?php

namespace App\Features\Subscriptions\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Renew Subscription.
 */
class RenewSubscriptionRequest extends FormRequest
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
            'subscription_plan_id' => ['required', 'exists:subscription_plans,id'],
            'phone_number' => ['required', 'string', 'max:20'],
        ];
    }
}
