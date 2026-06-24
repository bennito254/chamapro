<?php

declare(strict_types=1);

namespace App\Features\Admin\Services;

use App\Features\Admin\Models\SystemSetting;

/**
 * Resolves platform-level M-Pesa Daraja credentials for subscription billing.
 */
class PlatformMpesaSettingsService
{
    public const KEY_CONSUMER_KEY = 'mpesa_consumer_key';

    public const KEY_CONSUMER_SECRET = 'mpesa_consumer_secret';

    public const KEY_SHORTCODE = 'mpesa_shortcode';

    public const KEY_PASSKEY = 'mpesa_passkey';

    public const KEY_CALLBACK_URL = 'mpesa_callback_url';

    public const KEY_ENVIRONMENT = 'mpesa_environment';

    public const KEY_STK_ENABLED = 'mpesa_stk_enabled';

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return [
            self::KEY_CONSUMER_KEY,
            self::KEY_CONSUMER_SECRET,
            self::KEY_SHORTCODE,
            self::KEY_PASSKEY,
            self::KEY_CALLBACK_URL,
            self::KEY_ENVIRONMENT,
            self::KEY_STK_ENABLED,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        $stored = SystemSetting::query()
            ->whereIn('key', self::keys())
            ->pluck('value', 'key')
            ->all();

        return [
            self::KEY_CONSUMER_KEY => $stored[self::KEY_CONSUMER_KEY] ?? (string) config('services.mpesa.consumer_key', ''),
            self::KEY_CONSUMER_SECRET => $stored[self::KEY_CONSUMER_SECRET] ?? (string) config('services.mpesa.consumer_secret', ''),
            self::KEY_SHORTCODE => $stored[self::KEY_SHORTCODE] ?? (string) config('services.mpesa.shortcode', ''),
            self::KEY_PASSKEY => $stored[self::KEY_PASSKEY] ?? (string) config('services.mpesa.passkey', ''),
            self::KEY_CALLBACK_URL => $stored[self::KEY_CALLBACK_URL] ?? (string) config('services.mpesa.callback_url', ''),
            self::KEY_ENVIRONMENT => $stored[self::KEY_ENVIRONMENT] ?? (string) config('services.mpesa.environment', 'sandbox'),
            self::KEY_STK_ENABLED => $stored[self::KEY_STK_ENABLED] ?? '1',
        ];
    }

    public function isStkEnabled(): bool
    {
        return in_array(strtolower($this->all()[self::KEY_STK_ENABLED]), ['1', 'true', 'yes', 'on'], true);
    }

    public function isConfigured(): bool
    {
        $settings = $this->all();

        return filled($settings[self::KEY_CONSUMER_KEY])
            && filled($settings[self::KEY_CONSUMER_SECRET])
            && filled($settings[self::KEY_SHORTCODE])
            && filled($settings[self::KEY_PASSKEY])
            && filled($settings[self::KEY_CALLBACK_URL]);
    }

    public function usesStubMode(): bool
    {
        return ! $this->isConfigured() || ! $this->isStkEnabled();
    }

    public function baseUrl(): string
    {
        return $this->all()[self::KEY_ENVIRONMENT] === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * @param  array<string, string>  $settings
     */
    public function sync(array $settings): void
    {
        foreach (self::keys() as $key) {
            if (! array_key_exists($key, $settings)) {
                continue;
            }

            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => (string) $settings[$key]],
            );
        }
    }
}
