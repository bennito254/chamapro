<?php

namespace Database\Seeders;

use App\Features\Admin\Models\SmsProvider;
use Illuminate\Database\Seeder;

class SmsProviderSeeder extends Seeder
{
    public function run(): void
    {
        SmsProvider::query()->firstOrCreate(
            ['driver' => 'log', 'name' => 'Dummy Log Provider'],
            [
                'credentials' => [],
                'is_default' => true,
                'status' => 'active',
            ],
        );
    }
}
