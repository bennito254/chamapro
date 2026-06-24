<?php

use App\Enums\AccountType;
use App\Enums\AmountType;
use App\Enums\ContributionFrequency;
use App\Features\Banking\Models\BankAccount;
use App\Features\Contributions\Models\Contribution;
use App\Features\Contributions\Models\ContributionChannel;
use App\Features\Contributions\Models\ContributionType;
use App\Features\Contributions\Services\ContributionEligibilityService;
use App\Features\Contributions\Services\ContributionService;
use App\Features\Groups\Models\Group;
use App\Features\Ledger\Models\ChartOfAccount;
use App\Features\Members\Models\Member;
use App\Support\GroupContext;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    $this->group = Group::create([
        'name' => 'Test Group',
        'email' => 'test@example.com',
        'phone' => '+254700000001',
        'currency' => 'KES',
        'status' => 'active',
    ]);

    $groupContext = new GroupContext;
    $groupContext->set($this->group);
    $this->app->instance(GroupContext::class, $groupContext);

    $this->member = Member::create([
        'group_id' => $this->group->id,
        'membership_number' => 'M001',
        'full_name' => 'Test Member',
        'date_joined' => Carbon::today(),
        'status' => 'active',
    ]);

    $this->channel = ContributionChannel::create([
        'group_id' => $this->group->id,
        'name' => 'Cash',
        'is_system' => true,
    ]);

    $this->type = ContributionType::create([
        'group_id' => $this->group->id,
        'name' => 'Monthly',
        'default_amount' => 1000,
        'amount_type' => AmountType::Fixed,
        'frequency' => ContributionFrequency::Monthly,
        'status' => 'active',
        'save_to_bank' => true,
    ]);

    foreach ([
        ['code' => '1000', 'name' => 'Cash', 'type' => AccountType::Asset],
        ['code' => '1100', 'name' => 'Bank', 'type' => AccountType::Asset],
        ['code' => '4000', 'name' => 'Contribution Income', 'type' => AccountType::Income],
    ] as $account) {
        ChartOfAccount::create([
            'group_id' => $this->group->id,
            ...$account,
            'is_system' => true,
        ]);
    }

    BankAccount::create([
        'group_id' => $this->group->id,
        'bank_name' => 'Group Bank',
        'account_name' => 'Loan Fund',
        'account_number' => 'LOAN-FUND',
        'opening_balance' => 0,
        'current_balance' => 0,
        'chart_of_account_id' => ChartOfAccount::where('code', '1100')->value('id'),
        'status' => 'active',
    ]);
});

test('member who met required fixed contribution is not eligible again', function (): void {
    $service = app(ContributionEligibilityService::class);
    $date = '2026-06-15';

    Contribution::create([
        'group_id' => $this->group->id,
        'member_id' => $this->member->id,
        'contribution_type_id' => $this->type->id,
        'contribution_channel_id' => $this->channel->id,
        'amount' => 1000,
        'date' => $date,
    ]);

    expect($service->hasMetRequirement($this->member->id, $this->type, $date))->toBeTrue();

    expect(fn () => $service->assertCanRecord($this->member->id, $this->type, $date, 100))
        ->toThrow(InvalidArgumentException::class);
});

test('member can top up a partial fixed contribution until required amount is met', function (): void {
    $service = app(ContributionEligibilityService::class);
    $date = '2026-06-15';

    Contribution::create([
        'group_id' => $this->group->id,
        'member_id' => $this->member->id,
        'contribution_type_id' => $this->type->id,
        'contribution_channel_id' => $this->channel->id,
        'amount' => 400,
        'date' => $date,
    ]);

    expect($service->remainingAmount($this->member->id, $this->type, $date))->toBe(600.0);

    $service->assertCanRecord($this->member->id, $this->type, $date, 600);

    app(ContributionService::class)->record([
        'group_id' => $this->group->id,
        'member_id' => $this->member->id,
        'contribution_type_id' => $this->type->id,
        'contribution_channel_id' => $this->channel->id,
        'amount' => 600,
        'date' => $date,
    ]);

    expect($service->hasMetRequirement($this->member->id, $this->type, $date))->toBeTrue();
});
