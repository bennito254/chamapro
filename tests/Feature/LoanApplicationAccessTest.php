<?php

use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanApplication;
use App\Features\Loans\Models\LoanProduct;
use App\Features\Members\Models\Member;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed([SubscriptionPlanSeeder::class, RolesAndPermissionsSeeder::class]);
});

function provisionGroupWithLoanApplicant(): array
{
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user = User::factory()->create(['group_id' => $group->id, 'email_verified_at' => now()]);
    $user->assignRole('Treasurer');

    $member = Member::factory()->create(['group_id' => $group->id]);
    $product = LoanProduct::create([
        'group_id' => $group->id,
        'name' => 'Emergency Loan',
        'max_amount' => 50000,
        'max_multiplier' => 3,
        'interest_type' => 'percentage',
        'interest_value' => 10,
        'repayment_period' => 6,
        'grace_period' => 0,
        'status' => 'active',
    ]);

    return compact('group', 'user', 'member', 'product');
}

test('treasurer can access loan application pages', function () {
    ['user' => $user] = provisionGroupWithLoanApplicant();

    $this->actingAs($user)
        ->get(route('portal.loan-applications.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('portal.loan-applications.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('portal/loan-applications/create'));
});

test('treasurer can create a loan application', function () {
    ['user' => $user, 'group' => $group, 'member' => $member, 'product' => $product] = provisionGroupWithLoanApplicant();

    $response = $this->actingAs($user)
        ->post(route('portal.loan-applications.store'), [
            'member_id' => $member->id,
            'loan_product_id' => $product->id,
            'requested_amount' => 15000,
            'purpose' => 'School fees for children',
        ]);

    $application = LoanApplication::where('group_id', $group->id)
        ->where('member_id', $member->id)
        ->first();

    $loan = Loan::where('loan_application_id', $application->id)->first();

    $response->assertRedirect(route('portal.loans.show', $loan));

    expect($application)->not->toBeNull()
        ->and((float) $application->requested_amount)->toBe(15000.0)
        ->and($application->purpose)->toBe('School fees for children')
        ->and($application->status->value)->toBe('disbursed')
        ->and($loan)->not->toBeNull()
        ->and($loan->status->value)->toBe('active')
        ->and((float) $loan->principal_amount)->toBe(15000.0)
        ->and((float) $loan->outstanding_balance)->toBe(16500.0);
});
