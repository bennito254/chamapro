<?php

declare(strict_types=1);

namespace App\Features\Contributions\Services;

use App\DTOs\JournalEntryDTO;
use App\DTOs\JournalLineDTO;
use App\Features\Banking\Services\BankAccountService;
use App\Features\Contributions\Models\Contribution;
use App\Features\Ledger\Services\LedgerService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Domain service for Contribution.
 */
class ContributionService
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private LedgerService $ledgerService,
        private BankAccountService $bankAccountService,
        private ContributionEligibilityService $contributionEligibilityService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function record(array $data): Contribution
    {
        return DB::transaction(function () use ($data): Contribution {
            $contribution = Contribution::make($data);
            $contribution->loadMissing('contributionType');

            $type = $contribution->contributionType;

            if ($type !== null) {
                $this->contributionEligibilityService->assertCanRecord(
                    (int) $contribution->member_id,
                    $type,
                    $contribution->date->toDateString(),
                    (float) $contribution->amount,
                );
            }

            $contribution->save();
            $debitAccount = $this->resolveDebitAccountCode($contribution);

            $this->ledgerService->post(new JournalEntryDTO(
                description: "Contribution from member #{$contribution->member_id}",
                date: $contribution->date->toDateString(),
                lines: [
                    new JournalLineDTO($debitAccount, debit: (float) $contribution->amount),
                    new JournalLineDTO('4000', credit: (float) $contribution->amount),
                ],
                sourceType: Contribution::class,
                sourceId: $contribution->id,
                recordedBy: $contribution->recorded_by,
            ));

            if ($contribution->contributionType?->save_to_bank) {
                $this->bankAccountService->recordContributionDeposit($contribution);
            }

            return $contribution->load(['member', 'contributionType', 'contributionChannel']);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<int, Contribution>
     */
    public function recordMany(array $shared, array $entries, int $recordedBy): array
    {
        return DB::transaction(function () use ($shared, $entries, $recordedBy): array {
            $recorded = [];

            foreach ($entries as $entry) {
                $recorded[] = $this->record([
                    ...$shared,
                    ...$entry,
                    'recorded_by' => $recordedBy,
                ]);
            }

            return $recorded;
        });
    }

    /**
     * @param  Collection<int, Contribution>  $contributions
     * @return Collection<int, array<string, mixed>>
     */
    public function groupByType(Collection $contributions): Collection
    {
        return $contributions
            ->groupBy('contribution_type_id')
            ->map(function (Collection $items, int|string $typeId): array {
                $type = $items->first()?->contributionType;

                return [
                    'type' => [
                        'id' => (int) $typeId,
                        'name' => $type?->name ?? 'Unknown',
                    ],
                    'contributions_count' => $items->count(),
                    'total_amount' => (float) $items->sum('amount'),
                    'contributions' => $items
                        ->sortBy(fn (Contribution $contribution) => $contribution->member?->full_name ?? '')
                        ->values(),
                ];
            })
            ->sortBy(fn (array $group) => $group['type']['name'])
            ->values();
    }

    private function resolveDebitAccountCode(Contribution $contribution): string
    {
        return $contribution->contributionType?->save_to_bank
            ? '1100'
            : '1000';
    }
}
