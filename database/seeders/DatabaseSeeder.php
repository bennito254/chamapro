<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SuperAdminSeeder::class,
            SubscriptionPlanSeeder::class,
            PlatformMpesaSettingsSeeder::class,
            RolesAndPermissionsSeeder::class,
            SmsProviderSeeder::class,
            DemoGroupSeeder::class,
        ]);
    }
}
