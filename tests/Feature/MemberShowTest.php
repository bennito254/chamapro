<?php

use App\Enums\AmountType;
use App\Enums\ContributionFrequency;
use App\Enums\InterestType;
use App\Enums\LoanStatus;
use App\Features\Contributions\Models\Contribution;
use App\Features\Contributions\Models\ContributionChannel;
use App\Features\Contributions\Models\ContributionType;
use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanApplication;
use App\Features\Loans\Models\LoanProduct;
use App\Features\Loans\Models\LoanRepayment;
use App\Features\Members\Models\Member;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed([SubscriptionPlanSeeder::class, RolesAndPermissionsSeeder::class]);
});

test('member show page includes contributions loans and repayments', function () {
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user = User::factory()->create(['group_id' => $group->id, 'email_verified_at' => now()]);
    $user->assignRole('Treasurer');

    $member = Member::factory()->create(['group_id' => $group->id]);

    $channel = ContributionChannel::query()->where('group_id', $group->id)->first();
    $type = ContributionType::create([
        'group_id' => $group->id,
        'name' => 'Monthly',
        'default_amount' => 1000,
        'amount_type' => AmountType::Fixed,
        'frequency' => ContributionFrequency::Monthly,
        'status' => 'active',
        'save_to_bank' => true,
    ]);

    Contribution::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'contribution_type_id' => $type->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 1000,
        'date' => '2026-06-01',
    ]);

    Contribution::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'contribution_type_id' => $type->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 500,
        'date' => '2026-06-15',
    ]);

    $product = LoanProduct::create([
        'group_id' => $group->id,
        'name' => 'Standard Loan',
        'max_amount' => 50000,
        'max_multiplier' => 3,
        'interest_type' => InterestType::Percentage,
        'interest_value' => 10,
        'repayment_period' => 6,
        'grace_period' => 0,
        'status' => 'active',
    ]);

    $application = LoanApplication::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'loan_product_id' => $product->id,
        'requested_amount' => 10000,
        'status' => 'disbursed',
    ]);

    $loan = Loan::create([
        'group_id' => $group->id,
        'loan_application_id' => $application->id,
        'member_id' => $member->id,
        'loan_product_id' => $product->id,
        'product_name' => 'Standard Loan',
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

    LoanRepayment::create([
        'group_id' => $group->id,
        'loan_id' => $loan->id,
        'amount' => 2000,
        'principal_paid' => 1000,
        'interest_paid' => 1000,
        'balance_after' => 9000,
        'date' => now()->toDateString(),
    ]);

    $this->actingAs($user)
        ->get(route('portal.members.show', $member))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/members/show')
            ->has('activity.contributions_by_date', 2)
            ->has('activity.loans', 1)
            ->has('activity.repayments', 1)
            ->where('activity.summary.total_contributions', 1500)
            ->where('activity.summary.active_loans', 1)
            ->where('activity.summary.total_repaid', 2000));
});
