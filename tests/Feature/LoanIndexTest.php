<?php

use App\Enums\InterestType;
use App\Enums\LoanStatus;
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

function provisionGroupForLoanIndexTests(): array
{
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user = User::factory()->create(['group_id' => $group->id, 'email_verified_at' => now()]);
    $user->assignRole('Treasurer');

    return compact('group', 'user');
}

test('loans index can be filtered by search and status', function () {
    ['group' => $group, 'user' => $user] = provisionGroupForLoanIndexTests();

    $activeMember = Member::factory()->create([
        'group_id' => $group->id,
        'full_name' => 'Alice Active',
        'membership_number' => 'M100',
    ]);

    $closedMember = Member::factory()->create([
        'group_id' => $group->id,
        'full_name' => 'Bob Closed',
        'membership_number' => 'M200',
    ]);

    $product = LoanProduct::create([
        'group_id' => $group->id,
        'name' => 'Business Loan',
        'max_amount' => 50000,
        'max_multiplier' => 3,
        'interest_type' => InterestType::Percentage,
        'interest_value' => 10,
        'repayment_period' => 6,
        'grace_period' => 0,
        'status' => 'active',
    ]);

    $applicationForActive = LoanApplication::create([
        'group_id' => $group->id,
        'member_id' => $activeMember->id,
        'loan_product_id' => $product->id,
        'requested_amount' => 10000,
        'status' => 'disbursed',
    ]);

    $applicationForClosed = LoanApplication::create([
        'group_id' => $group->id,
        'member_id' => $closedMember->id,
        'loan_product_id' => $product->id,
        'requested_amount' => 5000,
        'status' => 'disbursed',
    ]);

    $activeLoan = Loan::create([
        'group_id' => $group->id,
        'loan_application_id' => $applicationForActive->id,
        'member_id' => $activeMember->id,
        'loan_product_id' => $product->id,
        'product_name' => 'Business Loan',
        'interest_type' => InterestType::Percentage,
        'interest_value' => 10,
        'repayment_period' => 6,
        'principal_amount' => 10000,
        'interest_amount' => 1000,
        'total_amount' => 11000,
        'outstanding_balance' => 11000,
        'disbursement_date' => now()->toDateString(),
        'due_date' => now()->addMonths(6)->toDateString(),
        'status' => LoanStatus::Active,
    ]);

    Loan::create([
        'group_id' => $group->id,
        'loan_application_id' => $applicationForClosed->id,
        'member_id' => $closedMember->id,
        'loan_product_id' => $product->id,
        'product_name' => 'Emergency Loan',
        'interest_type' => InterestType::Percentage,
        'interest_value' => 10,
        'repayment_period' => 6,
        'principal_amount' => 5000,
        'interest_amount' => 500,
        'total_amount' => 5500,
        'outstanding_balance' => 0,
        'disbursement_date' => now()->subMonths(2)->toDateString(),
        'due_date' => now()->addMonths(4)->toDateString(),
        'status' => LoanStatus::Closed,
    ]);

    $this->actingAs($user)
        ->get(route('portal.loans.index', ['search' => 'Alice']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/loans/index')
            ->has('loans.data', 1)
            ->where('loans.data.0.id', $activeLoan->id)
            ->where('filters.search', 'Alice'));

    $this->actingAs($user)
        ->get(route('portal.loans.index', ['status' => 'closed']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/loans/index')
            ->has('loans.data', 1)
            ->where('loans.data.0.member.full_name', 'Bob Closed'));
});
