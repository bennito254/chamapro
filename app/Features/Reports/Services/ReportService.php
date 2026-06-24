<?php

declare(strict_types=1);

namespace App\Features\Reports\Services;

use App\Enums\LoanStatus;
use App\Features\Banking\Models\BankAccount;
use App\Features\Banking\Models\BankTransaction;
use App\Features\Banking\Models\CashAccount;
use App\Features\Banking\Models\CashTransaction;
use App\Features\Contributions\Models\Contribution;
use App\Features\Expenses\Models\Expense;
use App\Features\Fines\Models\Fine;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanRepayment;
use App\Features\Members\Models\Member;
use App\Features\Shares\Models\SharePurchase;
use App\Features\Welfare\Models\WelfareContribution;
use App\Features\Welfare\Models\WelfareDisbursement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Domain service for Report.
 */
class ReportService
{
    /**
     * @return array<string, mixed>
     */
    public function memberStatement(Member $member, ?Carbon $from = null, ?Carbon $to = null): array
    {
        return [
            'member' => $member,
            'contributions' => $this->dateFilter(Contribution::query()->where('member_id', $member->id), $from, $to)->get(),
            'loans' => Loan::query()->where('member_id', $member->id)->get(),
            'repayments' => $this->dateFilter(
                LoanRepayment::query()->whereHas('loan', fn ($q) => $q->where('member_id', $member->id)),
                $from,
                $to,
            )->get(),
            'fines' => $this->dateFilter(Fine::query()->where('member_id', $member->id), $from, $to)->get(),
            'shares' => $this->dateFilter(SharePurchase::query()->where('member_id', $member->id), $from, $to)->get(),
            'welfare_contributions' => $this->dateFilter(WelfareContribution::query()->where('member_id', $member->id), $from, $to)->get(),
            'welfare_disbursements' => $this->dateFilter(WelfareDisbursement::query()->where('member_id', $member->id), $from, $to)->get(),
            'running_balance' => $this->calculateMemberBalance($member, $from, $to),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Contribution>
     */
    public function contributionsReport(array $filters = []): Collection
    {
        $query = Contribution::query()->with(['member', 'contributionType', 'contributionChannel']);

        if (isset($filters['member_id'])) {
            $query->where('member_id', $filters['member_id']);
        }

        if (isset($filters['contribution_type_id'])) {
            $query->where('contribution_type_id', $filters['contribution_type_id']);
        }

        if (isset($filters['contribution_channel_id'])) {
            $query->where('contribution_channel_id', $filters['contribution_channel_id']);
        }

        return $this->dateFilter(
            $query,
            $this->parseDate($filters['from'] ?? null),
            $this->parseDate($filters['to'] ?? null),
        )->get();
    }

    /**
     * @return Collection<int, Loan>
     */
    public function activeLoansReport(?Carbon $from = null, ?Carbon $to = null): Collection
    {
        return $this->dateFilter(
            Loan::query()->where('status', LoanStatus::Active)->with('member'),
            $from,
            $to,
            'disbursement_date',
        )->get();
    }

    /**
     * @return Collection<int, Loan>
     */
    public function closedLoansReport(?Carbon $from = null, ?Carbon $to = null): Collection
    {
        return $this->dateFilter(
            Loan::query()->where('status', LoanStatus::Closed)->with('member'),
            $from,
            $to,
            'disbursement_date',
        )->get();
    }

    /**
     * @return Collection<int, Loan>
     */
    public function loanAgingReport(): Collection
    {
        return Loan::query()
            ->where('status', LoanStatus::Active)
            ->whereNotNull('due_date')
            ->with('member')
            ->orderBy('due_date')
            ->get()
            ->map(function (Loan $loan): Loan {
                $loan->setAttribute('days_overdue', max(0, now()->diffInDays($loan->due_date, false) * -1));

                return $loan;
            });
    }

    /**
     * @return Collection<int, Loan>
     */
    public function loanDefaultersReport(): Collection
    {
        return Loan::query()
            ->where('status', LoanStatus::Active)
            ->where('due_date', '<', now())
            ->with('member')
            ->get();
    }

    /**
     * @return array<string, float|Collection<int, LoanRepayment>>
     */
    public function loanInterestEarnedReport(?Carbon $from = null, ?Carbon $to = null): array
    {
        $repayments = $this->dateFilter(LoanRepayment::query()->with('loan.member'), $from, $to)->get();

        return [
            'total_interest' => (float) $repayments->sum('interest_paid'),
            'repayments' => $repayments,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function repaymentsReport(?Carbon $from = null, ?Carbon $to = null): array
    {
        $repayments = $this->dateFilter(LoanRepayment::query()->with('loan.member'), $from, $to)->get();

        return [
            'total_repaid' => (float) $repayments->sum('amount'),
            'outstanding_balances' => (float) Loan::query()->where('status', LoanStatus::Active)->sum('outstanding_balance'),
            'partial_payments' => $repayments->filter(fn (LoanRepayment $r) => (float) $r->balance_after > 0)->values(),
            'repayments' => $repayments,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function finesReport(?Carbon $from = null, ?Carbon $to = null, ?bool $paid = null): array
    {
        $query = Fine::query()->with(['member', 'fineType']);
        $this->dateFilter($query, $from, $to);

        if ($paid !== null) {
            $query->where('is_paid', $paid);
        }

        $fines = $query->get();

        return [
            'paid' => $fines->where('is_paid', true),
            'unpaid' => $fines->where('is_paid', false),
            'fine_revenue' => (float) $fines->where('is_paid', true)->sum('amount'),
            'fines' => $fines,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function bankReport(?Carbon $from = null, ?Carbon $to = null): array
    {
        $transactions = $this->dateFilter(BankTransaction::query()->with('bankAccount'), $from, $to)->get();

        return [
            'deposits' => $transactions->where('type', 'deposit'),
            'withdrawals' => $transactions->where('type', 'withdrawal'),
            'transfers' => $transactions->where('type', 'transfer'),
            'current_balances' => BankAccount::query()->get(['id', 'bank_name', 'account_number', 'current_balance']),
            'transactions' => $transactions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function cashReport(?Carbon $from = null, ?Carbon $to = null): array
    {
        $transactions = $this->dateFilter(CashTransaction::query(), $from, $to)->get();

        return [
            'received' => $transactions->where('type', 'receive'),
            'paid' => $transactions->where('type', 'pay'),
            'current_position' => (float) CashAccount::query()->join(
                'chart_of_accounts',
                'cash_accounts.chart_of_account_id',
                '=',
                'chart_of_accounts.id',
            )->sum('chart_of_accounts.balance'),
            'transactions' => $transactions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function monthlyReport(int $year, int $month): array
    {
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        return [
            'period' => ['year' => $year, 'month' => $month],
            'contributions' => (float) Contribution::query()->whereBetween('date', [$from, $to])->sum('amount'),
            'loans_disbursed' => (float) Loan::query()->whereBetween('disbursement_date', [$from, $to])->sum('principal_amount'),
            'repayments' => (float) LoanRepayment::query()->whereBetween('date', [$from, $to])->sum('amount'),
            'fines' => (float) Fine::query()->whereBetween('date', [$from, $to])->sum('amount'),
            'welfare_contributions' => (float) WelfareContribution::query()->whereBetween('date', [$from, $to])->sum('amount'),
            'welfare_disbursements' => (float) WelfareDisbursement::query()->whereBetween('date', [$from, $to])->sum('amount'),
            'expenses' => (float) Expense::query()->whereBetween('date', [$from, $to])->sum('amount'),
            'bank' => $this->bankReport($from, $to),
            'cash' => $this->cashReport($from, $to),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function annualReport(int $year): array
    {
        $from = Carbon::create($year, 1, 1)->startOfYear();
        $to = $from->copy()->endOfYear();

        return [
            'year' => $year,
            'contribution_trends' => Contribution::query()
                ->whereBetween('date', [$from, $to])
                ->selectRaw("{$this->monthExpression('date')} as month, SUM(amount) as total")
                ->groupByRaw($this->monthExpression('date'))
                ->orderBy('month')
                ->get(),
            'member_growth' => Member::query()
                ->whereBetween('date_joined', [$from, $to])
                ->selectRaw("{$this->monthExpression('date_joined')} as month, COUNT(*) as total")
                ->groupByRaw($this->monthExpression('date_joined'))
                ->orderBy('month')
                ->get(),
            'loan_portfolio' => $this->activeLoansReport($from, $to),
            'fines' => $this->finesReport($from, $to),
            'interest_revenue' => $this->loanInterestEarnedReport($from, $to),
            'monthly_summaries' => collect(range(1, 12))->mapWithKeys(
                fn (int $month): array => [$month => $this->monthlyReport($year, $month)],
            ),
        ];
    }

    private function calculateMemberBalance(Member $member, ?Carbon $from, ?Carbon $to): float
    {
        $contributions = (float) $this->dateFilter(Contribution::query()->where('member_id', $member->id), $from, $to)->sum('amount');
        $repayments = (float) $this->dateFilter(
            LoanRepayment::query()->whereHas('loan', fn ($q) => $q->where('member_id', $member->id)),
            $from,
            $to,
        )->sum('amount');
        $fines = (float) $this->dateFilter(Fine::query()->where('member_id', $member->id)->where('is_paid', true), $from, $to)->sum('amount');
        $shares = (float) $this->dateFilter(SharePurchase::query()->where('member_id', $member->id), $from, $to)->sum('amount');
        $welfareIn = (float) $this->dateFilter(WelfareContribution::query()->where('member_id', $member->id), $from, $to)->sum('amount');
        $welfareOut = (float) $this->dateFilter(WelfareDisbursement::query()->where('member_id', $member->id), $from, $to)->sum('amount');
        $loansDisbursed = (float) Loan::query()->where('member_id', $member->id)->sum('principal_amount');

        return round($contributions + $fines + $shares + $welfareIn - $welfareOut - $loansDisbursed, 2);
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    private function dateFilter(Builder $query, ?Carbon $from, ?Carbon $to, string $column = 'date'): Builder
    {
        if ($from) {
            $query->where($column, '>=', $from);
        }

        if ($to) {
            $query->where($column, '<=', $to);
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    public function annualReportForDisplay(int $year): array
    {
        $from = Carbon::create($year, 1, 1)->startOfYear();
        $to = $from->copy()->endOfYear();

        $monthlyTotals = collect(range(1, 12))->map(function (int $month) use ($year): array {
            $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            return [
                'month' => $month,
                'contributions' => (float) Contribution::query()->whereBetween('date', [$monthStart, $monthEnd])->sum('amount'),
                'loans_disbursed' => (float) Loan::query()->whereBetween('disbursement_date', [$monthStart, $monthEnd])->sum('principal_amount'),
                'repayments' => (float) LoanRepayment::query()->whereBetween('date', [$monthStart, $monthEnd])->sum('amount'),
                'fines' => (float) Fine::query()->whereBetween('date', [$monthStart, $monthEnd])->sum('amount'),
                'expenses' => (float) Expense::query()->whereBetween('date', [$monthStart, $monthEnd])->sum('amount'),
            ];
        });

        $fines = $this->finesReport($from, $to);
        $interest = $this->loanInterestEarnedReport($from, $to);

        return [
            'year' => $year,
            'contribution_trends' => Contribution::query()
                ->whereBetween('date', [$from, $to])
                ->selectRaw("{$this->monthExpression('date')} as month, SUM(amount) as total")
                ->groupByRaw($this->monthExpression('date'))
                ->orderBy('month')
                ->get(),
            'member_growth' => Member::query()
                ->whereBetween('date_joined', [$from, $to])
                ->selectRaw("{$this->monthExpression('date_joined')} as month, COUNT(*) as total")
                ->groupByRaw($this->monthExpression('date_joined'))
                ->orderBy('month')
                ->get(),
            'active_loans' => Loan::query()->where('status', LoanStatus::Active)->count(),
            'fine_revenue' => $fines['fine_revenue'],
            'total_interest' => $interest['total_interest'],
            'monthly_totals' => $monthlyTotals,
        ];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        return Carbon::parse($value);
    }

    private function monthExpression(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%m', {$column}) AS INTEGER)",
            'pgsql' => "EXTRACT(MONTH FROM {$column})",
            default => "MONTH({$column})",
        };
    }
}
