<?php

declare(strict_types=1);

namespace App\Features\Reports\Services;

use App\Features\Banking\Models\BankAccount;
use App\Features\Banking\Models\BankTransaction;
use App\Features\Banking\Models\CashTransaction;
use App\Features\Contributions\Models\Contribution;
use App\Features\Fines\Models\Fine;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanRepayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Shapes raw report query results into presentation-friendly arrays for Inertia and exports.
 */
class ReportPresenter
{
    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public function present(string $type, array $raw): array
    {
        return match ($type) {
            'contributions' => $this->presentContributions($raw),
            'loans' => $this->presentLoans($raw, 'Active loans'),
            'closed_loans' => $this->presentLoans($raw, 'Closed loans'),
            'loan_aging' => $this->presentLoanAging(collect($raw['items'] ?? [])),
            'loan_defaulters' => $this->presentLoans($raw, 'Loan defaulters'),
            'repayments' => $this->presentRepayments($raw),
            'interest_earned' => $this->presentInterestEarned($raw),
            'fines' => $this->presentFines($raw),
            'bank' => $this->presentBank($raw),
            'cash' => $this->presentCash($raw),
            'monthly' => $this->presentMonthly($raw),
            'annual' => $this->presentAnnual($raw),
            default => $raw,
        };
    }

    /**
     * @return list<string>
     */
    public function exportHeaders(string $type): array
    {
        return match ($type) {
            'contributions' => ['Date', 'Member', 'Membership #', 'Type', 'Channel', 'Amount'],
            'loans', 'closed_loans', 'loan_defaulters' => ['Member', 'Membership #', 'Principal', 'Outstanding', 'Disbursed', 'Due date', 'Status'],
            'loan_aging' => ['Member', 'Membership #', 'Outstanding', 'Due date', 'Days overdue'],
            'repayments' => ['Date', 'Member', 'Amount', 'Principal', 'Interest', 'Balance after'],
            'interest_earned' => ['Date', 'Member', 'Interest paid'],
            'fines' => ['Date', 'Member', 'Type', 'Amount', 'Paid'],
            'bank' => ['Date', 'Account', 'Type', 'Amount', 'Reference'],
            'cash' => ['Date', 'Type', 'Amount', 'Description'],
            'monthly' => ['Metric', 'Amount'],
            'annual' => ['Month', 'Contributions', 'Loans disbursed', 'Repayments', 'Fines', 'Expenses'],
            default => ['Data'],
        };
    }

    /**
     * @param  array<string, mixed>  $presented
     * @return list<list<string|int|float|null>>
     */
    public function exportRows(string $type, array $presented): array
    {
        if (isset($presented['rows'])) {
            return collect($presented['rows'])
                ->map(fn (array $row): array => array_values($row))
                ->values()
                ->all();
        }

        if ($type === 'monthly' && isset($presented['summary'])) {
            return collect($presented['summary'])
                ->map(fn (array $item): array => [$item['label'], $item['value']])
                ->values()
                ->all();
        }

        if ($type === 'annual' && isset($presented['monthly_totals'])) {
            return collect($presented['monthly_totals'])
                ->map(fn (array $row): array => [
                    $row['month_label'],
                    $row['contributions'],
                    $row['loans_disbursed'],
                    $row['repayments'],
                    $row['fines'],
                    $row['expenses'],
                ])
                ->values()
                ->all();
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function presentContributions(array $raw): array
    {
        /** @var Collection<int, Contribution> $items */
        $items = $raw['items'] ?? collect();

        $rows = $items->map(fn (Contribution $contribution): array => [
            'date' => $contribution->date?->toDateString(),
            'member' => $contribution->member?->full_name,
            'membership_number' => $contribution->member?->membership_number,
            'type' => $contribution->contributionType?->name,
            'channel' => $contribution->contributionChannel?->name,
            'amount' => (float) $contribution->amount,
        ])->values()->all();

        return [
            'summary' => [
                $this->summaryItem('Total contributions', (float) $items->sum('amount'), 'currency'),
                $this->summaryItem('Records', $items->count(), 'number'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @param  Collection<int, Loan>|array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function presentLoans(Collection|array $raw, string $title): array
    {
        $items = $raw instanceof Collection ? $raw : collect($raw['items'] ?? []);

        $rows = $items->map(fn (Loan $loan): array => [
            'member' => $loan->member?->full_name,
            'membership_number' => $loan->member?->membership_number,
            'principal' => (float) $loan->principal_amount,
            'outstanding' => (float) $loan->outstanding_balance,
            'disbursed' => $loan->disbursement_date?->toDateString(),
            'due_date' => $loan->due_date?->toDateString(),
            'status' => $loan->status?->value ?? (string) $loan->status,
        ])->values()->all();

        return [
            'title' => $title,
            'summary' => [
                $this->summaryItem('Loans', $items->count(), 'number'),
                $this->summaryItem('Principal', (float) $items->sum('principal_amount'), 'currency'),
                $this->summaryItem('Outstanding', (float) $items->sum('outstanding_balance'), 'currency'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @param  Collection<int, Loan>  $items
     * @return array<string, mixed>
     */
    private function presentLoanAging(Collection $items): array
    {
        $rows = $items->map(fn (Loan $loan): array => [
            'member' => $loan->member?->full_name,
            'membership_number' => $loan->member?->membership_number,
            'outstanding' => (float) $loan->outstanding_balance,
            'due_date' => $loan->due_date?->toDateString(),
            'days_overdue' => (int) ($loan->days_overdue ?? 0),
        ])->values()->all();

        return [
            'summary' => [
                $this->summaryItem('Active overdue loans', $items->count(), 'number'),
                $this->summaryItem('Outstanding balance', (float) $items->sum('outstanding_balance'), 'currency'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function presentRepayments(array $raw): array
    {
        /** @var Collection<int, LoanRepayment> $repayments */
        $repayments = $raw['repayments'] ?? collect();

        $rows = $repayments->map(fn (LoanRepayment $repayment): array => [
            'date' => $repayment->date?->toDateString(),
            'member' => $repayment->loan?->member?->full_name,
            'amount' => (float) $repayment->amount,
            'principal' => (float) $repayment->principal_paid,
            'interest' => (float) $repayment->interest_paid,
            'balance_after' => (float) $repayment->balance_after,
        ])->values()->all();

        return [
            'summary' => [
                $this->summaryItem('Total repaid', (float) ($raw['total_repaid'] ?? 0), 'currency'),
                $this->summaryItem('Outstanding portfolio', (float) ($raw['outstanding_balances'] ?? 0), 'currency'),
                $this->summaryItem('Partial payments', $raw['partial_payments']?->count() ?? 0, 'number'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function presentInterestEarned(array $raw): array
    {
        /** @var Collection<int, LoanRepayment> $repayments */
        $repayments = $raw['repayments'] ?? collect();

        $rows = $repayments->map(fn (LoanRepayment $repayment): array => [
            'date' => $repayment->date?->toDateString(),
            'member' => $repayment->loan?->member?->full_name,
            'interest' => (float) $repayment->interest_paid,
        ])->values()->all();

        return [
            'summary' => [
                $this->summaryItem('Interest earned', (float) ($raw['total_interest'] ?? 0), 'currency'),
                $this->summaryItem('Repayment records', $repayments->count(), 'number'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function presentFines(array $raw): array
    {
        /** @var Collection<int, Fine> $fines */
        $fines = $raw['fines'] ?? collect();

        $rows = $fines->map(fn (Fine $fine): array => [
            'date' => $fine->date?->toDateString(),
            'member' => $fine->member?->full_name,
            'type' => $fine->fineType?->name,
            'amount' => (float) $fine->amount,
            'paid' => $fine->is_paid ? 'Yes' : 'No',
        ])->values()->all();

        return [
            'summary' => [
                $this->summaryItem('Fine revenue', (float) ($raw['fine_revenue'] ?? 0), 'currency'),
                $this->summaryItem('Paid fines', $raw['paid']?->count() ?? 0, 'number'),
                $this->summaryItem('Unpaid fines', $raw['unpaid']?->count() ?? 0, 'number'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function presentBank(array $raw): array
    {
        /** @var Collection<int, BankTransaction> $transactions */
        $transactions = $raw['transactions'] ?? collect();

        $rows = $transactions->map(fn (BankTransaction $transaction): array => [
            'date' => $transaction->date?->toDateString(),
            'account' => $transaction->bankAccount?->bank_name,
            'type' => $transaction->type,
            'amount' => (float) $transaction->amount,
            'reference' => $transaction->reference,
        ])->values()->all();

        /** @var Collection<int, BankAccount> $balances */
        $balances = $raw['current_balances'] ?? collect();

        return [
            'summary' => [
                $this->summaryItem('Deposits', (float) ($raw['deposits']?->sum('amount') ?? 0), 'currency'),
                $this->summaryItem('Withdrawals', (float) ($raw['withdrawals']?->sum('amount') ?? 0), 'currency'),
                $this->summaryItem('Current balance', (float) $balances->sum('current_balance'), 'currency'),
            ],
            'balances' => $balances->map(fn (BankAccount $account): array => [
                'bank' => $account->bank_name,
                'account_number' => $account->account_number,
                'balance' => (float) $account->current_balance,
            ])->values()->all(),
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function presentCash(array $raw): array
    {
        /** @var Collection<int, CashTransaction> $transactions */
        $transactions = $raw['transactions'] ?? collect();

        $rows = $transactions->map(fn (CashTransaction $transaction): array => [
            'date' => $transaction->date?->toDateString(),
            'type' => $transaction->type,
            'amount' => (float) $transaction->amount,
            'description' => $transaction->description,
        ])->values()->all();

        return [
            'summary' => [
                $this->summaryItem('Received', (float) ($raw['received']?->sum('amount') ?? 0), 'currency'),
                $this->summaryItem('Paid out', (float) ($raw['paid']?->sum('amount') ?? 0), 'currency'),
                $this->summaryItem('Cash position', (float) ($raw['current_position'] ?? 0), 'currency'),
            ],
            'rows' => $rows,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function presentMonthly(array $raw): array
    {
        $period = $raw['period'] ?? [];
        $month = (int) ($period['month'] ?? now()->month);
        $year = (int) ($period['year'] ?? now()->year);

        return [
            'period_label' => Carbon::create($year, $month, 1)->format('F Y'),
            'summary' => [
                $this->summaryItem('Contributions', (float) ($raw['contributions'] ?? 0), 'currency'),
                $this->summaryItem('Loans disbursed', (float) ($raw['loans_disbursed'] ?? 0), 'currency'),
                $this->summaryItem('Repayments', (float) ($raw['repayments'] ?? 0), 'currency'),
                $this->summaryItem('Fines', (float) ($raw['fines'] ?? 0), 'currency'),
                $this->summaryItem('Welfare in', (float) ($raw['welfare_contributions'] ?? 0), 'currency'),
                $this->summaryItem('Welfare out', (float) ($raw['welfare_disbursements'] ?? 0), 'currency'),
                $this->summaryItem('Expenses', (float) ($raw['expenses'] ?? 0), 'currency'),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function presentAnnual(array $raw): array
    {
        $monthlyTotals = collect($raw['monthly_totals'] ?? [])->map(function (array $row): array {
            $month = (int) $row['month'];

            return [
                ...$row,
                'month_label' => Carbon::create((int) ($raw['year'] ?? now()->year), $month, 1)->format('M'),
            ];
        })->values()->all();

        $contributionTrends = collect($raw['contribution_trends'] ?? [])->map(fn ($row): array => [
            'month' => (int) $row->month,
            'month_label' => Carbon::create((int) ($raw['year'] ?? now()->year), (int) $row->month, 1)->format('M'),
            'total' => (float) $row->total,
        ])->values()->all();

        $memberGrowth = collect($raw['member_growth'] ?? [])->map(fn ($row): array => [
            'month' => (int) $row->month,
            'month_label' => Carbon::create((int) ($raw['year'] ?? now()->year), (int) $row->month, 1)->format('M'),
            'total' => (int) $row->total,
        ])->values()->all();

        return [
            'year' => (int) ($raw['year'] ?? now()->year),
            'summary' => [
                $this->summaryItem('Active loans', (int) ($raw['active_loans'] ?? 0), 'number'),
                $this->summaryItem('Fine revenue', (float) ($raw['fine_revenue'] ?? 0), 'currency'),
                $this->summaryItem('Interest earned', (float) ($raw['total_interest'] ?? 0), 'currency'),
                $this->summaryItem('New members', collect($memberGrowth)->sum('total'), 'number'),
            ],
            'contribution_trends' => $contributionTrends,
            'member_growth' => $memberGrowth,
            'monthly_totals' => $monthlyTotals,
        ];
    }

    /**
     * @return array{label: string, value: float|int, format: string}
     */
    private function summaryItem(string $label, float|int $value, string $format): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'format' => $format,
        ];
    }
}
