<?php

declare(strict_types=1);

namespace App\Features\Loans\Services;

use App\Enums\LoanStatus;
use App\Features\Contributions\Models\Contribution;
use App\Features\Loans\Models\Loan;
use Illuminate\Database\Eloquent\Builder;

/**
 * Domain service for Loan Fund.
 */
class LoanFundService
{
    /**
     * Sum of contributions deposited to the bank (eligible for lending).
     */
    public function totalBankContributions(): float
    {
        return (float) $this->bankContributionsQuery()->sum('amount');
    }

    /**
     * Outstanding loan principal still owed by members.
     */
    public function totalOutstandingPrincipal(): float
    {
        return (float) Loan::query()
            ->where('status', LoanStatus::Active)
            ->sum('outstanding_balance');
    }

    /**
     * Group funds available for new loans (bank contributions minus outstanding principal).
     */
    public function availableForLoans(): float
    {
        return max(0, round($this->totalBankContributions() - $this->totalOutstandingPrincipal(), 2));
    }

    /**
     * @return Builder<Contribution>
     */
    private function bankContributionsQuery(): Builder
    {
        return Contribution::query()
            ->whereHas('contributionType', fn (Builder $query) => $query->where('save_to_bank', true));
    }
}
