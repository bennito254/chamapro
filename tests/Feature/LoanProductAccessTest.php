<?php

use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Loans\Models\LoanProduct;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed([SubscriptionPlanSeeder::class, RolesAndPermissionsSeeder::class]);
});

function provisionGroupWithChairperson(): array
{
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user = User::factory()->create(['group_id' => $group->id, 'email_verified_at' => now()]);
    $user->assignRole('Chairperson');

    return compact('group', 'user');
}

test('chairperson can access loan product pages', function () {
    ['user' => $user] = provisionGroupWithChairperson();

    $this->actingAs($user)
        ->get(route('portal.loan-products.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('portal.loan-products.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('portal/loan-products/create'));
});

test('chairperson can create a loan product', function () {
    ['user' => $user, 'group' => $group] = provisionGroupWithChairperson();

    $this->actingAs($user)
        ->post(route('portal.loan-products.store'), [
            'name' => 'Emergency Loan',
            'description' => 'Short-term emergency lending',
            'max_amount' => 50000,
            'max_multiplier' => 3,
            'interest_type' => 'percentage',
            'interest_value' => 10,
            'repayment_period' => 6,
            'grace_period' => 0,
            'status' => 'active',
        ])
        ->assertRedirect(route('portal.loan-products.index'));

    $product = LoanProduct::where('group_id', $group->id)->where('name', 'Emergency Loan')->first();

    expect($product)->not->toBeNull()
        ->and((float) $product->max_amount)->toBe(50000.0)
        ->and((float) $product->interest_value)->toBe(10.0)
        ->and($product->repayment_period)->toBe(6);
});
