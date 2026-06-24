<?php

use App\Features\Admin\Models\SmsProvider;
use App\Features\Auth\Models\SuperAdmin;
use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Database\Seeders\SuperAdminSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed([
        SubscriptionPlanSeeder::class,
        RolesAndPermissionsSeeder::class,
        SuperAdminSeeder::class,
    ]);
});

function actingAsSuperAdmin(): SuperAdmin
{
    $admin = SuperAdmin::first();

    test()->actingAs($admin, 'super_admin');

    return $admin;
}

test('super admin can manage subscription plans', function () {
    actingAsSuperAdmin();

    $this->get(route('admin.plans.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/plans/index'));

    $this->get(route('admin.plans.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/plans/create'));

    $this->post(route('admin.plans.store'), [
        'name' => 'Growth Plan',
        'billing_cycle' => 'monthly',
        'amount' => 2500,
        'discount_percentage' => 0,
        'max_members' => 100,
        'max_users' => 10,
        'trial_days' => 7,
        'status' => 'active',
    ])->assertRedirect(route('admin.plans.index'));

    $plan = SubscriptionPlan::where('name', 'Growth Plan')->first();

    expect($plan)->not->toBeNull();

    $this->get(route('admin.plans.edit', $plan))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/plans/edit')->where('plan.name', 'Growth Plan'));

    $this->put(route('admin.plans.update', $plan), [
        'name' => 'Growth Plus',
        'billing_cycle' => 'annual',
        'amount' => 25000,
        'discount_percentage' => 5,
        'max_members' => 120,
        'max_users' => 12,
        'trial_days' => 14,
        'status' => 'active',
    ])->assertRedirect(route('admin.plans.index'));

    expect($plan->fresh()->name)->toBe('Growth Plus');

    $this->delete(route('admin.plans.destroy', $plan))
        ->assertRedirect(route('admin.plans.index'));

    expect(SubscriptionPlan::find($plan->id))->toBeNull();
});

test('subscriptions index includes group owner contact details', function () {
    actingAsSuperAdmin();

    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create([
        'phone' => '254712345678',
        'email' => 'group@example.com',
    ]);

    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    $user = User::factory()->create([
        'group_id' => $group->id,
        'name' => 'Chair Person',
        'email' => 'chair@example.com',
        'email_verified_at' => now(),
    ]);

    app(RolesAndPermissionsSeeder::class)->seedForGroup($group);
    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user->assignRole('Chairperson');

    $this->get(route('admin.subscriptions.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/subscriptions/index')
            ->has('subscriptions.data', 1)
            ->where('subscriptions.data.0.owner.name', 'Chair Person')
            ->where('subscriptions.data.0.owner.phone', '254712345678')
            ->where('subscriptions.data.0.owner.email', 'chair@example.com'));
});

test('super admin can extend group subscription', function () {
    actingAsSuperAdmin();

    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    $subscription = $group->fresh()->activeSubscription;
    $originalEnd = $subscription->end_date->copy();

    $this->post(route('admin.groups.extend-subscription', $group), [
        'days' => '30',
    ])->assertRedirect();

    expect($subscription->fresh()->end_date->toDateString())
        ->toBe($originalEnd->addDays(30)->toDateString());
});

test('super admin can impersonate a group chairperson', function () {
    actingAsSuperAdmin();

    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    $chairperson = User::factory()->create([
        'group_id' => $group->id,
        'name' => 'Group Chair',
        'email' => 'chair@chama.test',
        'email_verified_at' => now(),
    ]);

    app(RolesAndPermissionsSeeder::class)->seedForGroup($group);
    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $chairperson->assignRole('Chairperson');

    $this->post(route('admin.impersonate', $group))
        ->assertRedirect(route('portal.dashboard'));

    expect(auth()->id())->toBe($chairperson->id);
});

test('super admin can send sms to group owners by subscription status', function () {
    actingAsSuperAdmin();

    SmsProvider::query()->create([
        'name' => 'Test Log Provider',
        'driver' => 'log',
        'credentials' => [],
        'is_default' => true,
        'status' => 'active',
    ]);

    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create(['phone' => '254700000001']);
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    $this->get(route('admin.owner-sms.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/owner-sms/create'));

    $this->post(route('admin.owner-sms.store'), [
        'subscription_status' => 'all',
        'body' => 'Your ChamaPro subscription reminder.',
    ])->assertRedirect(route('admin.owner-sms.create', ['subscription_status' => 'all']));

    $this->assertDatabaseHas('sms_messages', [
        'group_id' => $group->id,
        'recipient' => '254700000001',
        'body' => 'Your ChamaPro subscription reminder.',
        'status' => 'sent',
    ]);
});
