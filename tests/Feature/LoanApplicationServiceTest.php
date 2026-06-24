<?php

use App\Enums\AccountType;
use App\Enums\AmountType;
use App\Enums\ContributionFrequency;
use App\Enums\InterestType;
use App\Enums\LoanApplicationStatus;
use App\Features\Banking\Models\BankAccount;
use App\Features\Banking\Models\BankTransaction;
use App\Features\Contributions\Models\ContributionChannel;
use App\Features\Contributions\Models\ContributionType;
use App\Features\Contributions\Services\ContributionService;
use App\Features\Groups\Models\Group;
use App\Features\Ledger\Models\ChartOfAccount;
use App\Features\Ledger\Models\JournalEntry;
use App\Features\Loans\Models\LoanApplication;
use App\Features\Loans\Models\LoanProduct;
use App\Features\Loans\Services\LoanApplicationService;
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

    $this->product = LoanProduct::create([
        'group_id' => $this->group->id,
        'name' => 'Standard Loan',
        'max_amount' => 100000,
        'max_multiplier' => 3,
        'interest_type' => InterestType::Percentage,
        'interest_value' => 10,
        'repayment_period' => 12,
        'grace_period' => 0,
        'status' => 'active',
    ]);
});

it('auto approves a draft loan application', function (): void {
    $service = app(LoanApplicationService::class);

    $application = LoanApplication::create([
        'group_id' => $this->group->id,
        'member_id' => $this->member->id,
        'loan_product_id' => $this->product->id,
        'requested_amount' => 5000,
        'purpose' => 'Business stock',
        'status' => LoanApplicationStatus::Draft,
    ]);

    $approved = $service->autoApprove($application);

    expect($approved->status)->toBe(LoanApplicationStatus::Approved)
        ->and($approved->review_notes)->toBe('Automatically approved.')
        ->and($approved->reviewed_by)->toBeNull();
});

it('transitions loan applications through the approval workflow', function (): void {
    $service = app(LoanApplicationService::class);

    $application = LoanApplication::create([
        'group_id' => $this->group->id,
        'member_id' => $this->member->id,
        'loan_product_id' => $this->product->id,
        'requested_amount' => 5000,
        'status' => LoanApplicationStatus::Draft,
    ]);

    $service->submit($application);
    expect($application->fresh()->status)->toBe(LoanApplicationStatus::Submitted);

    $service->startReview($application);
    expect($application->fresh()->status)->toBe(LoanApplicationStatus::UnderReview);

    $service->approve($application, 'Looks good');
    expect($application->fresh()->status)->toBe(LoanApplicationStatus::Approved);
});

it('records contributions and posts ledger entries', function (): void {
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

    $channel = ContributionChannel::create([
        'group_id' => $this->group->id,
        'name' => 'Cash',
        'is_system' => true,
    ]);

    $type = ContributionType::create([
        'group_id' => $this->group->id,
        'name' => 'Monthly',
        'default_amount' => 1000,
        'amount_type' => AmountType::Fixed,
        'frequency' => ContributionFrequency::Monthly,
        'status' => 'active',
        'save_to_bank' => true,
    ]);

    $contribution = app(ContributionService::class)->record([
        'group_id' => $this->group->id,
        'member_id' => $this->member->id,
        'contribution_type_id' => $type->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 1000,
        'date' => Carbon::today(),
    ]);

    expect($contribution)->not->toBeNull()
        ->and(JournalEntry::count())->toBe(1)
        ->and(ChartOfAccount::where('code', '1100')->value('balance'))->toEqual(1000.0)
        ->and((float) BankAccount::query()
            ->where('group_id', $this->group->id)
            ->where('account_number', 'LOAN-FUND')
            ->value('current_balance'))->toEqual(1000.0);
});

it('posts non-bank contribution types to cash account', function (): void {
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

    $channel = ContributionChannel::create([
        'group_id' => $this->group->id,
        'name' => 'Cash',
        'is_system' => true,
    ]);

    $type = ContributionType::create([
        'group_id' => $this->group->id,
        'name' => 'Welfare Fund',
        'default_amount' => 200,
        'amount_type' => AmountType::Fixed,
        'frequency' => ContributionFrequency::Monthly,
        'status' => 'active',
        'save_to_bank' => false,
    ]);

    app(ContributionService::class)->record([
        'group_id' => $this->group->id,
        'member_id' => $this->member->id,
        'contribution_type_id' => $type->id,
        'contribution_channel_id' => $channel->id,
        'amount' => 200,
        'date' => Carbon::today(),
    ]);

    expect(ChartOfAccount::where('code', '1000')->value('balance'))->toEqual(200.0)
        ->and(ChartOfAccount::where('code', '1100')->value('balance'))->toEqual(0.0)
        ->and(BankTransaction::count())->toBe(0);
});

it('rejects invalid loan application transitions', function (): void {
    $service = app(LoanApplicationService::class);

    $application = LoanApplication::create([
        'group_id' => $this->group->id,
        'member_id' => $this->member->id,
        'loan_product_id' => $this->product->id,
        'requested_amount' => 5000,
        'status' => LoanApplicationStatus::Draft,
    ]);

    expect(fn () => $service->approve($application))
        ->toThrow(InvalidArgumentException::class);
});
