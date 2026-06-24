<?php

declare(strict_types=1);

namespace App\Features\Loans\Services;

use App\Enums\LoanApplicationStatus;
use App\Features\Loans\Models\LoanApplication;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Domain service for Loan Application.
 */
class LoanApplicationService
{
    /** @var array<string, list<LoanApplicationStatus>> */
    private const TRANSITIONS = [
        'draft' => [LoanApplicationStatus::Submitted],
        'submitted' => [LoanApplicationStatus::UnderReview],
        'under_review' => [
            LoanApplicationStatus::Recommended,
            LoanApplicationStatus::Rejected,
            LoanApplicationStatus::Approved,
        ],
        'recommended' => [
            LoanApplicationStatus::Approved,
            LoanApplicationStatus::Rejected,
        ],
        'approved' => [LoanApplicationStatus::Disbursed],
        'disbursed' => [LoanApplicationStatus::Closed],
        'rejected' => [LoanApplicationStatus::Closed],
    ];

    /**
     * Submit.
     */
    public function submit(LoanApplication $application): LoanApplication
    {
        return $this->transition($application, LoanApplicationStatus::Submitted);
    }

    /**
     * Start review.
     */
    public function startReview(LoanApplication $application, ?int $reviewedBy = null): LoanApplication
    {
        return $this->transition($application, LoanApplicationStatus::UnderReview, [
            'reviewed_by' => $reviewedBy,
        ]);
    }

    /**
     * Recommend.
     */
    public function recommend(LoanApplication $application, ?string $notes = null, ?int $reviewedBy = null): LoanApplication
    {
        return $this->transition($application, LoanApplicationStatus::Recommended, [
            'review_notes' => $notes,
            'reviewed_by' => $reviewedBy,
        ]);
    }

    /**
     * Approve.
     */
    public function approve(LoanApplication $application, ?string $notes = null, ?int $reviewedBy = null): LoanApplication
    {
        return $this->transition($application, LoanApplicationStatus::Approved, [
            'review_notes' => $notes,
            'reviewed_by' => $reviewedBy,
        ]);
    }

    /**
     * Auto approve.
     */
    public function autoApprove(LoanApplication $application, ?int $reviewedBy = null, ?string $notes = null): LoanApplication
    {
        $application = $this->submit($application);
        $application = $this->startReview($application, $reviewedBy);

        return $this->approve($application, $notes ?? 'Automatically approved.', $reviewedBy);
    }

    /**
     * Reject.
     */
    public function reject(LoanApplication $application, ?string $notes = null, ?int $reviewedBy = null): LoanApplication
    {
        return $this->transition($application, LoanApplicationStatus::Rejected, [
            'review_notes' => $notes,
            'reviewed_by' => $reviewedBy,
        ]);
    }

    /**
     * Mark disbursed.
     */
    public function markDisbursed(LoanApplication $application): LoanApplication
    {
        return $this->transition($application, LoanApplicationStatus::Disbursed);
    }

    /**
     * Close.
     */
    public function close(LoanApplication $application): LoanApplication
    {
        return $this->transition($application, LoanApplicationStatus::Closed);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function transition(
        LoanApplication $application,
        LoanApplicationStatus $to,
        array $attributes = [],
    ): LoanApplication {
        return DB::transaction(function () use ($application, $to, $attributes): LoanApplication {
            $allowed = self::TRANSITIONS[$application->status->value] ?? [];

            if (! in_array($to, $allowed, true)) {
                throw new InvalidArgumentException(
                    "Cannot transition loan application from {$application->status->value} to {$to->value}."
                );
            }

            $application->update([
                'status' => $to,
                ...$attributes,
            ]);

            return $application->fresh();
        });
    }
}
