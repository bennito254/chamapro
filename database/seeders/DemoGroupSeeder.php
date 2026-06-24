<?php

namespace Database\Seeders;

use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class DemoGroupSeeder extends Seeder
{
    public function run(): void
    {
        $plan = SubscriptionPlan::first();

        if (! $plan) {
            return;
        }

        $group = Group::firstOrCreate(
            ['email' => 'demo@chamapro.com'],
            [
                'name' => 'Demo Chama Group',
                'phone' => '+254700000000',
                'currency' => 'KES',
                'status' => 'active',
            ],
        );

        if (! $group->subscriptions()->exists()) {
            app(GroupProvisioningService::class)->provisionExisting($group, $plan);
        }

        app(RolesAndPermissionsSeeder::class)->seedForGroup($group);

        $user = User::firstOrCreate(
            ['email' => 'chair@demo.com', 'group_id' => $group->id],
            [
                'name' => 'Demo Chairperson',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );

        app()[PermissionRegistrar::class]->setPermissionsTeamId($group->id);

        if (! $user->hasRole('Chairperson')) {
            $user->assignRole('Chairperson');
        }
    }
}
