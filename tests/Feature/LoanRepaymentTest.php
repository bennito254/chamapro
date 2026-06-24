<?php

use App\Enums\AccountType;
use App\Enums\InterestType;
use App\Enums\LoanRepaymentType;
use App\Enums\LoanStatus;
use App\Features\Groups\Models\Group;
use App\Features\Ledger\Models\ChartOfAccount;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanApplication;
use App\Features\Loans\Models\LoanProduct;
use App\Features\Loans\Services\LoanRepaymentService;
use App\Features\Members\Models\Member;
use App\Support\GroupContext;

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
        'date_joined' => now()->toDateString(),
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

    $application = LoanApplication::create([
        'group_id' => $this->group->id,
        'member_id' => $this->member->id,
        'loan_product_id' => $this->product->id,
        'requested_amount' => 10000,
        'status' => 'approved',
    ]);

    $this->loan = Loan::create([
        'group_id' => $this->group->id,
        'loan_application_id' => $application->id,
        'member_id' => $this->member->id,
        'loan_product_id' => $this->product->id,
        'product_name' => 'Standard Loan',
        'interest_type' => InterestType::Percentage,
        'interest_value' => 10,
        'repayment_period' => 12,
        'grace_period' => 0,
        'principal_amount' => 10000,
        'interest_amount' => 1000,
        'total_amount' => 11000,
        'outstanding_balance' => 11000,
        'disbursement_date' => now()->toDateString(),
        'due_date' => now()->addMonths(12)->toDateString(),
        'status' => LoanStatus::Active,
    ]);

    foreach ([
        ['code' => '1000', 'name' => 'Cash', 'type' => AccountType::Asset],
        ['code' => '1200', 'name' => 'Loan Receivable', 'type' => AccountType::Asset],
        ['code' => '4200', 'name' => 'Interest Income', 'type' => AccountType::Income],
    ] as $account) {
        ChartOfAccount::create([
            'group_id' => $this->group->id,
            ...$account,
            'is_system' => true,
        ]);
    }
});

test('combined payment applies interest first then principal', function (): void {
    $service = app(LoanRepaymentService::class);

    $repayment = $service->record($this->loan, [
        'payment_type' => LoanRepaymentType::Combined->value,
        'amount' => 3500,
        'date' => now()->toDateString(),
    ]);

    expect((float) $repayment->interest_paid)->toBe(1000.0)
        ->and((float) $repayment->principal_paid)->toBe(2500.0)
        ->and($service->interestOutstanding($this->loan->fresh()))->toBe(0.0)
        ->and($service->principalOutstanding($this->loan->fresh()))->toBe(7500.0)
        ->and((float) $this->loan->fresh()->outstanding_balance)->toBe(7500.0);
});

test('combined payment can pay loan in full', function (): void {
    $service = app(LoanRepaymentService::class);

    $repayment = $service->record($this->loan, [
        'payment_type' => LoanRepaymentType::Combined->value,
        'amount' => 11000,
        'date' => now()->toDateString(),
    ]);

    expect((float) $repayment->interest_paid)->toBe(1000.0)
        ->and((float) $repayment->principal_paid)->toBe(10000.0)
        ->and((float) $repayment->balance_after)->toBe(0.0)
        ->and($this->loan->fresh()->status)->toBe(LoanStatus::Closed);
});

test('member can pay interest only without reducing principal', function (): void {
    $service = app(LoanRepaymentService::class);

    $repayment = $service->record($this->loan, [
        'payment_type' => LoanRepaymentType::Interest->value,
        'amount' => 400,
        'date' => now()->toDateString(),
    ]);

    expect((float) $repayment->interest_paid)->toBe(400.0)
        ->and((float) $repayment->principal_paid)->toBe(0.0)
        ->and($service->interestOutstanding($this->loan->fresh()))->toBe(600.0)
        ->and($service->principalOutstanding($this->loan->fresh()))->toBe(10000.0);
});

test('member can pay principal while interest is still outstanding', function (): void {
    $service = app(LoanRepaymentService::class);

    $service->record($this->loan, [
        'payment_type' => LoanRepaymentType::Interest->value,
        'amount' => 200,
        'date' => now()->toDateString(),
    ]);

    $repayment = $service->record($this->loan->fresh(), [
        'payment_type' => LoanRepaymentType::Principal->value,
        'amount' => 3000,
        'date' => now()->toDateString(),
    ]);

    expect((float) $repayment->interest_paid)->toBe(0.0)
        ->and((float) $repayment->principal_paid)->toBe(3000.0)
        ->and($service->interestOutstanding($this->loan->fresh()))->toBe(800.0)
        ->and($service->principalOutstanding($this->loan->fresh()))->toBe(7000.0);
});

test('interest payment cannot exceed outstanding interest', function (): void {
    $service = app(LoanRepaymentService::class);

    expect(fn () => $service->record($this->loan, [
        'payment_type' => LoanRepaymentType::Interest->value,
        'amount' => 1500,
        'date' => now()->toDateString(),
    ]))->toThrow(InvalidArgumentException::class);
});

test('principal payment cannot exceed outstanding principal', function (): void {
    $service = app(LoanRepaymentService::class);

    expect(fn () => $service->record($this->loan, [
        'payment_type' => LoanRepaymentType::Principal->value,
        'amount' => 15000,
        'date' => now()->toDateString(),
    ]))->toThrow(InvalidArgumentException::class);
});
