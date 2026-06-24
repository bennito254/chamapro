<?php

declare(strict_types=1);

namespace App\Features\Members\Services;

use App\Enums\LoanStatus;
use App\Features\Contributions\Models\Contribution;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanRepayment;
use App\Features\Members\Models\Member;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Domain service for Member Activity.
 */
class MemberActivityService
{
    /**
     * @return array<string, mixed>
     */
    public function forMember(Member $member): array
    {
        $contributionsByDate = $this->contributionsByDate($member);
        $loans = Loan::query()
            ->where('member_id', $member->id)
            ->with('loanProduct')
            ->orderByDesc('disbursement_date')
            ->get();

        $repayments = LoanRepayment::query()
            ->whereHas('loan', fn ($query) => $query->where('member_id', $member->id))
            ->with('loan')
            ->orderByDesc('date')
            ->get();

        return [
            'contributions_by_date' => $contributionsByDate,
            'loans' => $loans,
            'repayments' => $repayments,
            'summary' => [
                'total_contributions' => (float) Contribution::query()
                    ->where('member_id', $member->id)
                    ->sum('amount'),
                'contributions_count' => Contribution::query()
                    ->where('member_id', $member->id)
                    ->count(),
                'loans_count' => $loans->count(),
                'active_loans' => $loans->where('status', LoanStatus::Active)->count(),
                'loan_outstanding' => (float) $loans
                    ->where('status', LoanStatus::Active)
                    ->sum('outstanding_balance'),
                'total_repaid' => (float) $repayments->sum('amount'),
            ],
        ];
    }

    /**
     * @return Collection<int, array{date: string, contributions_count: int, total_amount: float}>
     */
    private function contributionsByDate(Member $member): Collection
    {
        return Contribution::query()
            ->where('member_id', $member->id)
            ->selectRaw('date, COUNT(*) as contributions_count, COALESCE(SUM(amount), 0) as total_amount')
            ->groupBy('date')
            ->orderByDesc('date')
            ->get()
            ->map(fn ($row) => [
                'date' => Carbon::parse($row->date)->toDateString(),
                'contributions_count' => (int) $row->contributions_count,
                'total_amount' => (float) $row->total_amount,
            ]);
    }
}
