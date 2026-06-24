<?php

declare(strict_types=1);

namespace App\Features\Contributions\Services;

use App\Enums\AmountType;
use App\Features\Contributions\Models\Contribution;
use App\Features\Contributions\Models\ContributionType;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Domain service for Contribution Eligibility.
 */
class ContributionEligibilityService
{
    /**
     * Required amount.
     */
    public function requiredAmount(ContributionType $type): ?float
    {
        if ($type->amount_type !== AmountType::Fixed || $type->default_amount === null) {
            return null;
        }

        $required = round((float) $type->default_amount, 2);

        if ($required <= 0) {
            return null;
        }

        return $required;
    }

    /**
     * Contributed amount.
     */
    public function contributedAmount(int $memberId, int $contributionTypeId, string $date): float
    {
        return round((float) Contribution::query()
            ->where('member_id', $memberId)
            ->where('contribution_type_id', $contributionTypeId)
            ->whereDate('date', $date)
            ->sum('amount'), 2);
    }

    /**
     * Has met requirement.
     */
    public function hasMetRequirement(int $memberId, ContributionType $type, string $date): bool
    {
        $contributed = $this->contributedAmount($memberId, $type->id, $date);
        $required = $this->requiredAmount($type);

        if ($required !== null) {
            return $contributed >= $required;
        }

        return $contributed > 0;
    }

    /**
     * Remaining amount.
     */
    public function remainingAmount(int $memberId, ContributionType $type, string $date): ?float
    {
        if ($this->hasMetRequirement($memberId, $type, $date)) {
            return 0.0;
        }

        $required = $this->requiredAmount($type);

        if ($required === null) {
            return null;
        }

        return max(0, round($required - $this->contributedAmount($memberId, $type->id, $date), 2));
    }

    /**
     * Assert can record.
     */
    public function assertCanRecord(int $memberId, ContributionType $type, string $date, float $amount): void
    {
        if ($this->hasMetRequirement($memberId, $type, $date)) {
            throw new InvalidArgumentException(
                "{$type->name} for this meeting is already complete for the selected member.",
            );
        }

        $remaining = $this->remainingAmount($memberId, $type, $date);

        if ($remaining !== null && $amount > $remaining) {
            throw new InvalidArgumentException(
                "Amount exceeds the remaining required contribution of {$remaining} for {$type->name}.",
            );
        }
    }

    /**
     * @param  Collection<int, int>  $memberIds
     * @return array<int, array<int, array{contributed: float, required: float|null, met: bool, remaining: float|null}>>
     */
    public function memberTotalsForDate(Collection $memberIds, string $date): array
    {
        if ($memberIds->isEmpty()) {
            return [];
        }

        $types = ContributionType::query()->where('status', 'active')->get()->keyBy('id');

        $totals = Contribution::query()
            ->whereIn('member_id', $memberIds)
            ->whereDate('date', $date)
            ->selectRaw('member_id, contribution_type_id, COALESCE(SUM(amount), 0) as total')
            ->groupBy('member_id', 'contribution_type_id')
            ->get()
            ->groupBy('member_id');

        $summaries = [];

        foreach ($memberIds as $memberId) {
            $summaries[$memberId] = [];

            foreach ($types as $type) {
                $contributed = round((float) ($totals->get($memberId)?->firstWhere('contribution_type_id', $type->id)?->total ?? 0), 2);
                $required = $this->requiredAmount($type);
                $met = $required !== null
                    ? $contributed >= $required
                    : $contributed > 0;
                $remaining = $met
                    ? 0.0
                    : ($required !== null ? max(0, round($required - $contributed, 2)) : null);

                $summaries[$memberId][$type->id] = [
                    'contributed' => $contributed,
                    'required' => $required,
                    'met' => $met,
                    'remaining' => $remaining,
                ];
            }
        }

        return $summaries;
    }
}
