<?php

use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Spatie\Permission\PermissionRegistrar;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('portal.dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $this->seed([SubscriptionPlanSeeder::class, RolesAndPermissionsSeeder::class]);

    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    $user = User::factory()->create([
        'group_id' => $group->id,
        'email_verified_at' => now(),
    ]);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user->assignRole('Chairperson');

    $this->actingAs($user);

    $response = $this->get(route('portal.dashboard'));
    $response->assertOk();
});
