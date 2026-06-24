<?php

declare(strict_types=1);

namespace App\Features\Loans\Services;

use App\DTOs\JournalEntryDTO;
use App\DTOs\JournalLineDTO;
use App\Enums\LoanRepaymentType;
use App\Enums\LoanStatus;
use App\Features\Ledger\Services\LedgerService;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanRepayment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Domain service for Loan Repayment.
 */
class LoanRepaymentService
{
    /**
     * Create a new instance.
     */
    public function __construct(private LedgerService $ledgerService) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function record(Loan $loan, array $data): LoanRepayment
    {
        if ($loan->status !== LoanStatus::Active) {
            throw new InvalidArgumentException('Repayments can only be recorded against active loans.');
        }

        $amount = (float) $data['amount'];
        $paymentType = LoanRepaymentType::from($data['payment_type']);

        if ($amount <= 0) {
            throw new InvalidArgumentException('Repayment amount must be greater than zero.');
        }

        if ($amount > (float) $loan->outstanding_balance) {
            throw new InvalidArgumentException('Repayment amount exceeds outstanding balance.');
        }

        return DB::transaction(function () use ($loan, $data, $amount, $paymentType): LoanRepayment {
            [$interestPaid, $principalPaid] = $this->allocatePayment($loan, $amount, $paymentType);
            $balanceAfter = round((float) $loan->outstanding_balance - $amount, 2);

            $repayment = LoanRepayment::create([
                'group_id' => $loan->group_id,
                'loan_id' => $loan->id,
                'amount' => $amount,
                'principal_paid' => $principalPaid,
                'interest_paid' => $interestPaid,
                'balance_after' => $balanceAfter,
                'date' => $data['date'],
                'method' => $data['method'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $data['recorded_by'] ?? null,
            ]);

            $lines = [
                new JournalLineDTO('1000', debit: $amount),
            ];

            if ($principalPaid > 0) {
                $lines[] = new JournalLineDTO('1200', credit: $principalPaid);
            }

            if ($interestPaid > 0) {
                $lines[] = new JournalLineDTO('4200', credit: $interestPaid);
            }

            $this->ledgerService->post(new JournalEntryDTO(
                description: "Loan repayment for loan #{$loan->id}",
                date: $repayment->date->toDateString(),
                lines: $lines,
                sourceType: LoanRepayment::class,
                sourceId: $repayment->id,
                recordedBy: $repayment->recorded_by,
            ));

            $loan->update([
                'outstanding_balance' => $balanceAfter,
                'status' => $balanceAfter <= 0 ? LoanStatus::Closed : LoanStatus::Active,
            ]);

            return $repayment->load('loan');
        });
    }

    /**
     * Interest outstanding.
     */
    public function interestOutstanding(Loan $loan): float
    {
        return max(0, round(
            (float) $loan->interest_amount - (float) $loan->repayments()->sum('interest_paid'),
            2,
        ));
    }

    /**
     * Principal outstanding.
     */
    public function principalOutstanding(Loan $loan): float
    {
        return max(0, round(
            (float) $loan->principal_amount - (float) $loan->repayments()->sum('principal_paid'),
            2,
        ));
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function allocatePayment(Loan $loan, float $amount, LoanRepaymentType $paymentType): array
    {
        return match ($paymentType) {
            LoanRepaymentType::Combined => $this->allocateCombinedPayment($loan, $amount),
            LoanRepaymentType::Interest => $this->allocateInterestPayment($loan, $amount),
            LoanRepaymentType::Principal => $this->allocatePrincipalPayment($loan, $amount),
        };
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function allocateCombinedPayment(Loan $loan, float $amount): array
    {
        $interestDue = $this->interestOutstanding($loan);
        $interestPaid = round(min($amount, $interestDue), 2);
        $remaining = round($amount - $interestPaid, 2);
        $principalDue = $this->principalOutstanding($loan);
        $principalPaid = round(min($remaining, $principalDue), 2);

        return [$interestPaid, $principalPaid];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function allocateInterestPayment(Loan $loan, float $amount): array
    {
        $interestDue = $this->interestOutstanding($loan);

        if ($interestDue <= 0) {
            throw new InvalidArgumentException('This loan has no outstanding interest.');
        }

        if ($amount > $interestDue) {
            throw new InvalidArgumentException("Interest payment cannot exceed outstanding interest of {$interestDue}.");
        }

        return [round($amount, 2), 0.0];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function allocatePrincipalPayment(Loan $loan, float $amount): array
    {
        $principalDue = $this->principalOutstanding($loan);

        if ($principalDue <= 0) {
            throw new InvalidArgumentException('This loan has no outstanding principal.');
        }

        if ($amount > $principalDue) {
            throw new InvalidArgumentException("Principal payment cannot exceed outstanding principal of {$principalDue}.");
        }

        return [0.0, round($amount, 2)];
    }
}
