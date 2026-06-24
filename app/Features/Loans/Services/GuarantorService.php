<?php

declare(strict_types=1);

namespace App\Features\Loans\Services;

use App\Enums\LoanApplicationStatus;
use App\Enums\LoanStatus;
use App\Features\Contributions\Models\Contribution;
use App\Features\Loans\Models\LoanGuarantor;
use App\Features\Loans\Models\LoanProduct;
use App\Features\Members\Models\Member;

/**
 * Domain service for Guarantor.
 */
class GuarantorService
{
    /**
     * Calculate exposure.
     */
    public function calculateExposure(Member $member): float
    {
        $pendingStatuses = [
            LoanApplicationStatus::Submitted,
            LoanApplicationStatus::UnderReview,
            LoanApplicationStatus::Recommended,
            LoanApplicationStatus::Approved,
        ];

        $applicationExposure = LoanGuarantor::query()
            ->where('member_id', $member->id)
            ->whereHas('loanApplication', fn ($query) => $query->whereIn('status', $pendingStatuses))
            ->sum('guaranteed_amount');

        $activeLoanExposure = LoanGuarantor::query()
            ->where('member_id', $member->id)
            ->whereHas('loanApplication.loan', fn ($query) => $query->where('status', LoanStatus::Active))
            ->sum('guaranteed_amount');

        return (float) $applicationExposure + (float) $activeLoanExposure;
    }

    /**
     * Calculate capacity.
     */
    public function calculateCapacity(Member $member, LoanProduct $product): float
    {
        $bankContributions = (float) Contribution::query()
            ->where('member_id', $member->id)
            ->whereHas('contributionType', fn ($query) => $query->where('save_to_bank', true))
            ->sum('amount');

        $maxCapacity = $bankContributions * (float) $product->max_multiplier;

        return max(0, $maxCapacity - $this->calculateExposure($member));
    }

    /**
     * Has capacity.
     */
    public function hasCapacity(Member $member, LoanProduct $product, float $guaranteedAmount): bool
    {
        return $this->calculateCapacity($member, $product) >= $guaranteedAmount;
    }
}
