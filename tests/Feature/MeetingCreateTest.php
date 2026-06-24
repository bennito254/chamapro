<?php

use App\Features\Contributions\Models\Contribution;
use App\Features\Contributions\Models\ContributionChannel;
use App\Features\Contributions\Models\ContributionType;
use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanApplication;
use App\Features\Loans\Models\LoanProduct;
use App\Features\Loans\Models\LoanRepayment;
use App\Features\Meetings\Models\Meeting;
use App\Features\Members\Models\Member;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    $this->seed([SubscriptionPlanSeeder::class, RolesAndPermissionsSeeder::class]);
});

function provisionGroupWithSecretary(): array
{
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user = User::factory()->create(['group_id' => $group->id, 'email_verified_at' => now()]);
    $user->assignRole('Secretary');

    return compact('group', 'user');
}

test('create meeting form is prepopulated with todays title and date', function () {
    ['user' => $user] = provisionGroupWithSecretary();
    $today = now();

    $this->actingAs($user)
        ->get(route('portal.meetings.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/meetings/create')
            ->where('defaults.title', $today->format('l j, F Y'))
            ->where('defaults.date', $today->toDateString())
            ->where('defaults.status', 'scheduled')
        );
});

test('secretary can create meeting without location or agenda', function () {
    ['user' => $user, 'group' => $group] = provisionGroupWithSecretary();
    $today = now();

    $this->actingAs($user)
        ->post(route('portal.meetings.store'), [
            'title' => $today->format('l j, F Y'),
            'date' => $today->toDateString(),
            'status' => 'scheduled',
        ])
        ->assertRedirect(route('portal.meetings.index'));

    expect(Meeting::where('group_id', $group->id)->where('title', $today->format('l j, F Y'))->exists())->toBeTrue();
});

test('meeting show page includes summary data', function () {
    ['user' => $user, 'group' => $group] = provisionGroupWithSecretary();
    $date = '2026-03-10';

    $member = Member::factory()->create(['group_id' => $group->id, 'status' => 'active']);
    $type = ContributionType::where('group_id', $group->id)->first();
    $channel = ContributionChannel::where('group_id', $group->id)->first();
    $product = LoanProduct::create([
        'group_id' => $group->id,
        'name' => 'Emergency Loan',
        'max_amount' => 100000,
        'max_multiplier' => 3,
        'interest_type' => 'percentage',
        'interest_value' => 10,
        'repayment_period' => 6,
        'grace_period' => 0,
        'status' => 'active',
    ]);

    $application = LoanApplication::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'loan_product_id' => $product->id,
        'requested_amount' => 5000,
        'status' => 'approved',
    ]);

    $meeting = Meeting::create([
        'group_id' => $group->id,
        'title' => 'Tuesday 10, March 2026',
        'date' => $date,
        'status' => 'scheduled',
        'created_by' => $user->id,
    ]);

    Contribution::create([
        'group_id' => $group->id,
        'member_id' => $member->id,
        'contribution_type_id' => $type->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 1000,
        'date' => $date,
        'recorded_by' => $user->id,
    ]);

    $loan = Loan::create([
        'group_id' => $group->id,
        'loan_application_id' => $application->id,
        'member_id' => $member->id,
        'loan_product_id' => $product->id,
        'product_name' => 'Emergency Loan',
        'interest_type' => 'percentage',
        'interest_value' => 10,
        'repayment_period' => 6,
        'grace_period' => 0,
        'principal_amount' => 5000,
        'interest_amount' => 500,
        'total_amount' => 5500,
        'outstanding_balance' => 5500,
        'disbursement_date' => $date,
        'due_date' => now()->addMonths(6)->toDateString(),
        'status' => 'active',
        'disbursed_by' => $user->id,
    ]);

    LoanRepayment::create([
        'group_id' => $group->id,
        'loan_id' => $loan->id,
        'amount' => 1100,
        'principal_paid' => 1000,
        'interest_paid' => 100,
        'balance_after' => 4500,
        'date' => $date,
        'method' => 'cash',
        'recorded_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('portal.meetings.show', $meeting))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('portal/meetings/show')
            ->has('summary.attendance')
            ->has('summary.contributions', 1)
            ->has('summary.contributions_by_type', 1)
            ->has('summary.loans_disbursed', 1)
            ->has('summary.loan_repayments', 1)
            ->where('summary.totals.contributions', 1000)
            ->where('summary.totals.loans_disbursed', 5000)
            ->where('summary.totals.interest_repaid', 100)
            ->where('summary.totals.principal_repaid', 1000)
            ->where('summary.totals.net_cash_in', -3900)
            ->where('summary.attendance.present', 1)
        );
});
