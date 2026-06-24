<?php

use App\Enums\SubscriptionStatus;
use App\Features\Auth\Models\SuperAdmin;
use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Members\Models\Member;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        SubscriptionPlanSeeder::class,
        RolesAndPermissionsSeeder::class,
    ]);
});

function createTenantUser(Group $group, string $role = 'Chairperson'): User
{
    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);

    $user = User::factory()->create([
        'group_id' => $group->id,
        'email_verified_at' => now(),
    ]);

    $user->assignRole($role);

    return $user;
}

function createProvisionedGroup(): Group
{
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();

    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    return $group->fresh();
}

test('tenant users cannot access another groups members', function () {
    $groupA = createProvisionedGroup();
    $groupB = createProvisionedGroup();

    $userA = createTenantUser($groupA);
    Member::factory()->create(['group_id' => $groupB->id]);

    $this->actingAs($userA);

    $response = $this->get(route('portal.members.index'));

    $response->assertOk();
    expect(Member::count())->toBe(0);
});

test('super admin guard is separate from tenant guard', function () {
    $admin = SuperAdmin::factory()->create();

    $response = $this->actingAs($admin, 'super_admin')
        ->get(route('admin.dashboard'));

    $response->assertOk();
});

test('tenant user cannot access admin dashboard', function () {
    $group = createProvisionedGroup();
    $user = createTenantUser($group);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect();
});

test('expired subscription blocks member creation', function () {
    $group = createProvisionedGroup();
    $user = createTenantUser($group);

    $group->activeSubscription->update([
        'status' => SubscriptionStatus::Expired,
        'end_date' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->post(route('portal.members.store'), [
            'membership_number' => 'M001',
            'full_name' => 'Test Member',
            'date_joined' => now()->toDateString(),
            'status' => 'active',
        ])
        ->assertRedirect(route('portal.subscription.renew'));
});

test('active subscription allows member creation', function () {
    $group = createProvisionedGroup();
    $user = createTenantUser($group);

    $this->actingAs($user)
        ->post(route('portal.members.store'), [
            'membership_number' => 'M001',
            'full_name' => 'Test Member',
            'date_joined' => now()->toDateString(),
            'status' => 'active',
        ])
        ->assertRedirect();

    expect(Member::where('group_id', $group->id)->count())->toBe(1);
});
