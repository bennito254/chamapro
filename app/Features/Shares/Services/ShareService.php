<?php

declare(strict_types=1);

namespace App\Features\Shares\Services;

use App\DTOs\JournalEntryDTO;
use App\DTOs\JournalLineDTO;
use App\Features\Ledger\Services\LedgerService;
use App\Features\Members\Models\Member;
use App\Features\Shares\Models\SharePurchase;
use App\Features\Shares\Models\ShareSetting;
use Illuminate\Support\Facades\DB;

/**
 * Domain service for Share.
 */
class ShareService
{
    /**
     * Create a new instance.
     */
    public function __construct(private LedgerService $ledgerService) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function purchase(array $data): SharePurchase
    {
        return DB::transaction(function () use ($data): SharePurchase {
            $settings = ShareSetting::query()->firstOrFail();
            $shares = $data['shares'] ?? (int) floor((float) $data['amount'] / (float) $settings->share_value);

            $purchase = SharePurchase::create([
                ...$data,
                'shares' => $shares,
                'amount' => $data['amount'] ?? $shares * (float) $settings->share_value,
            ]);

            $this->ledgerService->post(new JournalEntryDTO(
                description: "Share purchase by member #{$purchase->member_id}",
                date: $purchase->date->toDateString(),
                lines: [
                    new JournalLineDTO('1000', debit: (float) $purchase->amount),
                    new JournalLineDTO('3100', credit: (float) $purchase->amount),
                ],
                sourceType: SharePurchase::class,
                sourceId: $purchase->id,
                recordedBy: $purchase->recorded_by,
            ));

            return $purchase->load('member');
        });
    }

    /**
     * Ownership percentage.
     */
    public function ownershipPercentage(Member $member): float
    {
        $totalShares = (int) SharePurchase::query()->sum('shares');

        if ($totalShares === 0) {
            return 0.0;
        }

        $memberShares = (int) SharePurchase::query()
            ->where('member_id', $member->id)
            ->sum('shares');

        return round(($memberShares / $totalShares) * 100, 4);
    }
}
