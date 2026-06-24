<?php

namespace Database\Seeders;

use App\Features\Admin\Models\SystemSetting;
use App\Features\Admin\Services\PlatformMpesaSettingsService;
use Illuminate\Database\Seeder;

class PlatformMpesaSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            PlatformMpesaSettingsService::KEY_CONSUMER_KEY => (string) config('services.mpesa.consumer_key', ''),
            PlatformMpesaSettingsService::KEY_CONSUMER_SECRET => (string) config('services.mpesa.consumer_secret', ''),
            PlatformMpesaSettingsService::KEY_SHORTCODE => (string) config('services.mpesa.shortcode', ''),
            PlatformMpesaSettingsService::KEY_PASSKEY => (string) config('services.mpesa.passkey', ''),
            PlatformMpesaSettingsService::KEY_CALLBACK_URL => (string) config('services.mpesa.callback_url', ''),
            PlatformMpesaSettingsService::KEY_ENVIRONMENT => (string) config('services.mpesa.environment', 'sandbox'),
            PlatformMpesaSettingsService::KEY_STK_ENABLED => '1',
        ];

        foreach ($defaults as $key => $value) {
            SystemSetting::query()->firstOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }
    }
}
