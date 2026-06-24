<?php

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
use App\Features\Loans\Services\GuarantorService;
use App\Features\Loans\Services\LoanFundService;
use App\Features\Members\Models\Member;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;

beforeEach(function () {
    $this->seed([SubscriptionPlanSeeder::class, RolesAndPermissionsSeeder::class]);
});

test('loan fund only counts bank-saved contributions', function () {
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    $member = Member::factory()->create(['group_id' => $group->id]);
    $monthlyType = ContributionType::where('group_id', $group->id)->where('name', 'Monthly Contribution')->first();
    $welfareType = ContributionType::where('group_id', $group->id)->where('name', 'Welfare Fund')->first();
    $channel = ContributionChannel::where('group_id', $group->id)->first();

    Contribution::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'contribution_type_id' => $monthlyType->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 5000,
        'date' => now()->toDateString(),
        'recorded_by' => null,
    ]);

    Contribution::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'contribution_type_id' => $welfareType->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 1000,
        'date' => now()->toDateString(),
        'recorded_by' => null,
    ]);

    $service = app(LoanFundService::class);

    expect($service->totalBankContributions())->toEqual(5000.0)
        ->and($service->availableForLoans())->toEqual(5000.0);
});

test('loan fund subtracts outstanding loan principal', function () {
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    $member = Member::factory()->create(['group_id' => $group->id]);
    $monthlyType = ContributionType::where('group_id', $group->id)->where('name', 'Monthly Contribution')->first();
    $channel = ContributionChannel::where('group_id', $group->id)->first();
    $product = LoanProduct::create([
        'group_id' => $group->id,
        'name' => 'Standard Loan',
        'max_amount' => 100000,
        'max_multiplier' => 3,
        'interest_type' => InterestType::Percentage,
        'interest_value' => 10,
        'repayment_period' => 12,
        'grace_period' => 0,
        'status' => 'active',
    ]);

    Contribution::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'contribution_type_id' => $monthlyType->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 10000,
        'date' => now()->toDateString(),
        'recorded_by' => null,
    ]);

    $application = LoanApplication::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'loan_product_id' => $product->id,
        'requested_amount' => 3000,
        'status' => 'approved',
    ]);

    Loan::create([
        'group_id' => $group->id,
        'loan_application_id' => $application->id,
        'member_id' => $member->id,
        'loan_product_id' => $product->id,
        'product_name' => 'Standard',
        'interest_type' => InterestType::Percentage,
        'interest_value' => 10,
        'repayment_period' => 12,
        'grace_period' => 0,
        'principal_amount' => 3000,
        'interest_amount' => 300,
        'total_amount' => 3300,
        'outstanding_balance' => 2500,
        'disbursement_date' => now()->toDateString(),
        'due_date' => now()->addMonths(12)->toDateString(),
        'status' => LoanStatus::Active,
    ]);

    expect(app(LoanFundService::class)->availableForLoans())->toEqual(7500.0);
});

test('guarantor capacity ignores non-bank contributions', function () {
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    $member = Member::factory()->create(['group_id' => $group->id]);
    $monthlyType = ContributionType::where('group_id', $group->id)->where('name', 'Monthly Contribution')->first();
    $welfareType = ContributionType::where('group_id', $group->id)->where('name', 'Welfare Fund')->first();
    $channel = ContributionChannel::where('group_id', $group->id)->first();
    $product = LoanProduct::create([
        'group_id' => $group->id,
        'name' => 'Standard Loan',
        'max_amount' => 100000,
        'max_multiplier' => 3,
        'interest_type' => InterestType::Percentage,
        'interest_value' => 10,
        'repayment_period' => 12,
        'grace_period' => 0,
        'status' => 'active',
    ]);

    Contribution::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'contribution_type_id' => $monthlyType->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 2000,
        'date' => now()->toDateString(),
        'recorded_by' => null,
    ]);

    Contribution::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'contribution_type_id' => $welfareType->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 8000,
        'date' => now()->toDateString(),
        'recorded_by' => null,
    ]);

    $capacity = app(GuarantorService::class)->calculateCapacity($member, $product);

    expect($capacity)->toEqual(6000.0);
});
