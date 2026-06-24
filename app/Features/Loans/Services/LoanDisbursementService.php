<?php

declare(strict_types=1);

namespace App\Features\Loans\Services;

use App\DTOs\JournalEntryDTO;
use App\DTOs\JournalLineDTO;
use App\Enums\InterestType;
use App\Enums\LoanApplicationStatus;
use App\Enums\LoanStatus;
use App\Features\Ledger\Services\LedgerService;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanApplication;
use App\Support\GroupContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Domain service for Loan Disbursement.
 */
class LoanDisbursementService
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private GroupContext $groupContext,
        private LedgerService $ledgerService,
        private LoanApplicationService $loanApplicationService,
    ) {}

    /**
     * Disburse.
     */
    public function disburse(
        LoanApplication $application,
        ?Carbon $disbursementDate = null,
        ?int $disbursedBy = null,
        string $debitAccountCode = '1000',
    ): Loan {
        if ($application->status !== LoanApplicationStatus::Approved) {
            throw new InvalidArgumentException('Only approved loan applications can be disbursed.');
        }

        if ($application->loan()->exists()) {
            throw new InvalidArgumentException('This loan application has already been disbursed.');
        }

        return DB::transaction(function () use ($application, $disbursementDate, $disbursedBy, $debitAccountCode): Loan {
            $product = $application->loanProduct;
            $principal = (float) $application->requested_amount;
            $interest = $this->calculateInterest($principal, $product->interest_type, (float) $product->interest_value);
            $total = $principal + $interest;
            $date = $disbursementDate ?? now();
            $dueDate = $date->copy()->addMonths($product->repayment_period);

            $loan = Loan::create([
                'group_id' => $this->groupContext->id(),
                'loan_application_id' => $application->id,
                'member_id' => $application->member_id,
                'loan_product_id' => $product->id,
                'product_name' => $product->name,
                'interest_type' => $product->interest_type,
                'interest_value' => $product->interest_value,
                'repayment_period' => $product->repayment_period,
                'grace_period' => $product->grace_period,
                'principal_amount' => $principal,
                'interest_amount' => $interest,
                'total_amount' => $total,
                'outstanding_balance' => $total,
                'disbursement_date' => $date,
                'due_date' => $dueDate,
                'status' => LoanStatus::Active,
                'disbursed_by' => $disbursedBy,
            ]);

            $this->ledgerService->post(new JournalEntryDTO(
                description: "Loan disbursement for member #{$application->member_id}",
                date: $date->toDateString(),
                lines: [
                    new JournalLineDTO('1200', debit: $principal),
                    new JournalLineDTO($debitAccountCode, credit: $principal),
                ],
                sourceType: Loan::class,
                sourceId: $loan->id,
                recordedBy: $disbursedBy,
            ));

            $this->loanApplicationService->markDisbursed($application);

            return $loan->load('member');
        });
    }

    private function calculateInterest(float $principal, InterestType $type, float $value): float
    {
        return match ($type) {
            InterestType::Percentage => round($principal * ($value / 100), 2),
            InterestType::Fixed => round($value, 2),
        };
    }
}
