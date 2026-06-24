<?php

namespace App\Features\Admin\Requests;

use App\Features\Admin\Services\PlatformMpesaSettingsService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request validation for Update Mpesa Settings.
 */
class UpdateMpesaSettingsRequest extends FormRequest
{
    /**
     * Authorize.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'mpesa_stk_enabled' => $this->boolean('mpesa_stk_enabled'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mpesa_consumer_key' => ['nullable', 'string', 'max:255'],
            'mpesa_consumer_secret' => ['nullable', 'string', 'max:255'],
            'mpesa_shortcode' => ['nullable', 'string', 'max:20'],
            'mpesa_passkey' => ['nullable', 'string', 'max:255'],
            'mpesa_callback_url' => ['nullable', 'url', 'max:500'],
            'mpesa_environment' => ['required', 'in:sandbox,production'],
            'mpesa_stk_enabled' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function mpesaSettings(): array
    {
        $validated = $this->validated();

        return [
            PlatformMpesaSettingsService::KEY_CONSUMER_KEY => $validated['mpesa_consumer_key'] ?? '',
            PlatformMpesaSettingsService::KEY_CONSUMER_SECRET => $validated['mpesa_consumer_secret'] ?? '',
            PlatformMpesaSettingsService::KEY_SHORTCODE => $validated['mpesa_shortcode'] ?? '',
            PlatformMpesaSettingsService::KEY_PASSKEY => $validated['mpesa_passkey'] ?? '',
            PlatformMpesaSettingsService::KEY_CALLBACK_URL => $validated['mpesa_callback_url'] ?? '',
            PlatformMpesaSettingsService::KEY_ENVIRONMENT => $validated['mpesa_environment'],
            PlatformMpesaSettingsService::KEY_STK_ENABLED => $validated['mpesa_stk_enabled'] ? '1' : '0',
        ];
    }
}
