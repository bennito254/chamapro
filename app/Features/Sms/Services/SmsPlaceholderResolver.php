<?php

declare(strict_types=1);

namespace App\Features\Sms\Services;

use App\Features\Contributions\Services\ContributionEligibilityService;
use App\Features\Fines\Models\Fine;
use App\Features\Groups\Models\Group;
use App\Features\Loans\Models\Loan;
use App\Features\Loans\Models\LoanRepayment;
use App\Features\Meetings\Models\Meeting;
use App\Features\Members\Models\Member;
use App\Features\Sms\Enums\SmsPlaceholder;
use App\Support\GroupContext;

/**
 * Resolves dynamic values for the application for Sms Placeholder.
 */
class SmsPlaceholderResolver
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private GroupContext $groupContext,
        private ContributionEligibilityService $contributionEligibility,
    ) {}

    /**
     * @return array<string, string>
     */
    public function resolve(Member $member): array
    {
        $group = Group::query()->find($this->groupContext->id());

        return [
            SmsPlaceholder::Name->value => $member->full_name,
            SmsPlaceholder::MembershipNumber->value => $member->membership_number,
            SmsPlaceholder::Phone->value => $member->phone_number ?? '',
            SmsPlaceholder::GroupName->value => $group?->name ?? '',
            SmsPlaceholder::ContributionsMissed->value => (string) $this->contributionsMissed($member),
            SmsPlaceholder::ContributionsDue->value => number_format($this->contributionsDue($member), 2),
            SmsPlaceholder::PrincipalBalance->value => number_format($this->principalBalance($member), 2),
            SmsPlaceholder::InterestBalance->value => number_format($this->interestBalance($member), 2),
            SmsPlaceholder::LoanBalance->value => number_format($this->loanBalance($member), 2),
            SmsPlaceholder::UnpaidFines->value => number_format($this->unpaidFines($member), 2),
        ];
    }

    private function contributionsMissed(Member $member): int
    {
        $date = $this->latestMeetingDate();

        if ($date === null) {
            return 0;
        }

        $totals = $this->contributionEligibility->memberTotalsForDate(collect([$member->id]), $date);
        $summary = $totals[$member->id] ?? [];

        return collect($summary)->filter(fn (array $row) => ! $row['met'])->count();
    }

    private function contributionsDue(Member $member): float
    {
        $date = $this->latestMeetingDate();

        if ($date === null) {
            return 0.0;
        }

        $totals = $this->contributionEligibility->memberTotalsForDate(collect([$member->id]), $date);
        $summary = $totals[$member->id] ?? [];

        return round(collect($summary)->sum(fn (array $row) => $row['remaining'] ?? 0), 2);
    }

    private function principalBalance(Member $member): float
    {
        $disbursed = (float) Loan::query()
            ->where('member_id', $member->id)
            ->where('status', 'active')
            ->sum('principal_amount');

        $principalRepaid = (float) LoanRepayment::query()
            ->whereHas('loan', fn ($query) => $query->where('member_id', $member->id))
            ->sum('principal_paid');

        return round(max(0, $disbursed - $principalRepaid), 2);
    }

    private function interestBalance(Member $member): float
    {
        $interestAmount = (float) Loan::query()
            ->where('member_id', $member->id)
            ->where('status', 'active')
            ->sum('interest_amount');

        $interestRepaid = (float) LoanRepayment::query()
            ->whereHas('loan', fn ($query) => $query->where('member_id', $member->id))
            ->sum('interest_paid');

        return round(max(0, $interestAmount - $interestRepaid), 2);
    }

    private function loanBalance(Member $member): float
    {
        return round((float) Loan::query()
            ->where('member_id', $member->id)
            ->where('status', 'active')
            ->sum('outstanding_balance'), 2);
    }

    private function unpaidFines(Member $member): float
    {
        return round((float) Fine::query()
            ->where('member_id', $member->id)
            ->where('is_paid', false)
            ->sum('amount'), 2);
    }

    private function latestMeetingDate(): ?string
    {
        $meeting = Meeting::query()->orderByDesc('date')->first();

        return $meeting?->date?->toDateString();
    }
}
