<?php

declare(strict_types=1);

namespace App\Features\Meetings\Services;

use App\Features\Contributions\Models\Contribution;
use App\Features\Contributions\Services\ContributionService;
use App\Features\Expenses\Models\Expense;
use App\Features\Fines\Models\Fine;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanRepayment;
use App\Features\Meetings\Models\Meeting;
use App\Features\Meetings\Models\MeetingAttendee;
use App\Features\Members\Models\Member;
use App\Features\Shares\Models\SharePurchase;
use App\Features\Welfare\Models\WelfareContribution;
use App\Features\Welfare\Models\WelfareDisbursement;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Domain service for Meeting Summary.
 */
class MeetingSummaryService
{
    /**
     * Create a new instance.
     */
    public function __construct(private ContributionService $contributionService) {}

    /**
     * @return array<string, mixed>
     */
    public function summarize(Meeting $meeting): array
    {
        $date = $meeting->date->copy()->startOfDay();

        $contributions = Contribution::query()
            ->with(['member', 'contributionType', 'contributionChannel'])
            ->whereDate('date', $date)
            ->orderBy('member_id')
            ->get();

        $loansDisbursed = Loan::query()
            ->with(['member', 'loanProduct'])
            ->whereDate('disbursement_date', $date)
            ->orderBy('member_id')
            ->get();

        $loanRepayments = LoanRepayment::query()
            ->with(['loan.member', 'loan.loanProduct'])
            ->whereDate('date', $date)
            ->orderBy('loan_id')
            ->get();

        $finesPaid = Fine::query()
            ->with(['member', 'fineType'])
            ->where('is_paid', true)
            ->whereDate('paid_at', $date)
            ->orderBy('member_id')
            ->get();

        $sharePurchases = SharePurchase::query()
            ->with('member')
            ->whereDate('date', $date)
            ->orderBy('member_id')
            ->get();

        $welfareContributions = WelfareContribution::query()
            ->with('member')
            ->whereDate('date', $date)
            ->orderBy('member_id')
            ->get();

        $welfareDisbursements = WelfareDisbursement::query()
            ->with('member')
            ->whereDate('date', $date)
            ->orderBy('member_id')
            ->get();

        $expenses = Expense::query()
            ->with('expenseCategory')
            ->whereDate('date', $date)
            ->get();

        $attendance = $this->buildAttendance($meeting, $date, $contributions);

        $contributionsTotal = (float) $contributions->sum('amount');
        $loansDisbursedTotal = (float) $loansDisbursed->sum('principal_amount');
        $principalRepaidTotal = (float) $loanRepayments->sum('principal_paid');
        $interestRepaidTotal = (float) $loanRepayments->sum('interest_paid');
        $repaymentsTotal = (float) $loanRepayments->sum('amount');
        $finesTotal = (float) $finesPaid->sum('amount');
        $sharesTotal = (float) $sharePurchases->sum('amount');
        $welfareInTotal = (float) $welfareContributions->sum('amount');
        $welfareOutTotal = (float) $welfareDisbursements->sum('amount');
        $expensesTotal = (float) $expenses->sum('amount');

        $memberSummaries = $this->buildMemberSummaries(
            $date,
            $attendance['records'],
            $contributions,
            $loansDisbursed,
            $loanRepayments,
            $finesPaid,
            $sharePurchases,
            $welfareContributions,
            $welfareDisbursements,
        );

        return [
            'attendance' => $attendance,
            'contributions' => $contributions,
            'contributions_by_type' => $this->contributionService->groupByType($contributions),
            'loans_disbursed' => $loansDisbursed,
            'loan_repayments' => $loanRepayments,
            'fines_paid' => $finesPaid,
            'share_purchases' => $sharePurchases,
            'welfare_contributions' => $welfareContributions,
            'welfare_disbursements' => $welfareDisbursements,
            'expenses' => $expenses,
            'member_summaries' => $memberSummaries,
            'totals' => [
                'contributions' => $contributionsTotal,
                'loans_disbursed' => $loansDisbursedTotal,
                'principal_repaid' => $principalRepaidTotal,
                'interest_repaid' => $interestRepaidTotal,
                'repayments' => $repaymentsTotal,
                'fines' => $finesTotal,
                'shares' => $sharesTotal,
                'welfare_in' => $welfareInTotal,
                'welfare_out' => $welfareOutTotal,
                'expenses' => $expensesTotal,
                'net_cash_in' => round(
                    $contributionsTotal + $finesTotal + $sharesTotal + $welfareInTotal + $interestRepaidTotal
                    - $loansDisbursedTotal - $welfareOutTotal - $expensesTotal,
                    2,
                ),
                'bank_contributions' => (float) $contributions
                    ->filter(fn (Contribution $contribution) => $contribution->contributionType?->save_to_bank)
                    ->sum('amount'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAttendance(Meeting $meeting, CarbonInterface $date, Collection $contributions): array
    {
        $activeMembers = Member::query()
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'membership_number']);

        $attendeeRecords = MeetingAttendee::query()
            ->where('meeting_id', $meeting->id)
            ->get()
            ->keyBy('member_id');

        $contributorIds = $contributions->pluck('member_id')->unique();

        $records = $activeMembers->map(function (Member $member) use ($attendeeRecords, $contributorIds) {
            $record = $attendeeRecords->get($member->id);

            if ($record) {
                $status = in_array($record->status, ['present', 'attended'], true) ? 'present' : 'absent';
            } elseif ($contributorIds->contains($member->id)) {
                $status = 'present';
            } else {
                $status = 'not_recorded';
            }

            return [
                'member_id' => $member->id,
                'full_name' => $member->full_name,
                'membership_number' => $member->membership_number,
                'status' => $status,
                'notes' => $record?->notes,
            ];
        })->values();

        $present = $records->where('status', 'present')->count();
        $absent = $records->where('status', 'absent')->count();
        $totalMembers = $records->count();

        return [
            'total_members' => $totalMembers,
            'present' => $present,
            'absent' => $absent,
            'not_recorded' => $records->where('status', 'not_recorded')->count(),
            'turnout_rate' => $totalMembers > 0 ? round(($present / $totalMembers) * 100, 1) : 0,
            'records' => $records,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $attendanceRecords
     * @return Collection<int, array<string, mixed>>
     */
    private function buildMemberSummaries(
        CarbonInterface $date,
        Collection $attendanceRecords,
        Collection $contributions,
        Collection $loansDisbursed,
        Collection $loanRepayments,
        Collection $finesPaid,
        Collection $sharePurchases,
        Collection $welfareContributions,
        Collection $welfareDisbursements,
    ): Collection {
        $memberIds = collect()
            ->merge($attendanceRecords->where('status', 'present')->pluck('member_id'))
            ->merge($contributions->pluck('member_id'))
            ->merge($loansDisbursed->pluck('member_id'))
            ->merge($loanRepayments->map(fn (LoanRepayment $repayment) => $repayment->loan?->member_id))
            ->merge($finesPaid->pluck('member_id'))
            ->merge($sharePurchases->pluck('member_id'))
            ->merge($welfareContributions->pluck('member_id'))
            ->merge($welfareDisbursements->pluck('member_id'))
            ->filter()
            ->unique()
            ->values();

        $members = Member::query()
            ->whereIn('id', $memberIds)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'membership_number'])
            ->keyBy('id');

        return $memberIds->map(function (int $memberId) use (
            $date,
            $members,
            $contributions,
            $loansDisbursed,
            $loanRepayments,
            $finesPaid,
            $sharePurchases,
            $welfareContributions,
            $welfareDisbursements,
        ) {
            $member = $members->get($memberId);

            $contributionsAmount = (float) $contributions->where('member_id', $memberId)->sum('amount');
            $loansTaken = (float) $loansDisbursed->where('member_id', $memberId)->sum('principal_amount');
            $memberRepayments = $loanRepayments->filter(fn (LoanRepayment $r) => $r->loan?->member_id === $memberId);
            $principalRepaid = (float) $memberRepayments->sum('principal_paid');
            $interestRepaid = (float) $memberRepayments->sum('interest_paid');
            $finesAmount = (float) $finesPaid->where('member_id', $memberId)->sum('amount');
            $sharesAmount = (float) $sharePurchases->where('member_id', $memberId)->sum('amount');
            $welfareIn = (float) $welfareContributions->where('member_id', $memberId)->sum('amount');
            $welfareOut = (float) $welfareDisbursements->where('member_id', $memberId)->sum('amount');

            return [
                'member_id' => $memberId,
                'full_name' => $member?->full_name,
                'membership_number' => $member?->membership_number,
                'contributions' => $contributionsAmount,
                'loans_taken' => $loansTaken,
                'principal_repaid' => $principalRepaid,
                'interest_repaid' => $interestRepaid,
                'fines' => $finesAmount,
                'shares' => $sharesAmount,
                'welfare_in' => $welfareIn,
                'welfare_out' => $welfareOut,
                'loan_outstanding' => $this->memberLoanOutstanding($memberId, $date),
                'net_position' => round(
                    $contributionsAmount + $finesAmount + $sharesAmount + $welfareIn
                    - $loansTaken - $welfareOut,
                    2,
                ),
            ];
        })->values();
    }

    private function memberLoanOutstanding(int $memberId, CarbonInterface $asOf): float
    {
        $disbursed = (float) Loan::query()
            ->where('member_id', $memberId)
            ->whereDate('disbursement_date', '<=', $asOf)
            ->sum('principal_amount');

        $principalRepaid = (float) LoanRepayment::query()
            ->whereHas('loan', fn ($query) => $query->where('member_id', $memberId))
            ->whereDate('date', '<=', $asOf)
            ->sum('principal_paid');

        return round(max(0, $disbursed - $principalRepaid), 2);
    }
}
