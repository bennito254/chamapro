<?php

declare(strict_types=1);

namespace App\Features\Fines\Services;

use App\DTOs\JournalEntryDTO;
use App\DTOs\JournalLineDTO;
use App\Features\Fines\Models\Fine;
use App\Features\Ledger\Services\LedgerService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Domain service for Fine.
 */
class FineService
{
    /**
     * Create a new instance.
     */
    public function __construct(private LedgerService $ledgerService) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Fine
    {
        return Fine::create($data);
    }

    /**
     * Record payment.
     */
    public function recordPayment(Fine $fine, ?int $recordedBy = null): Fine
    {
        if ($fine->is_paid) {
            throw new InvalidArgumentException('This fine has already been paid.');
        }

        return DB::transaction(function () use ($fine, $recordedBy): Fine {
            $fine->update([
                'is_paid' => true,
                'paid_at' => now()->toDateString(),
            ]);

            $this->ledgerService->post(new JournalEntryDTO(
                description: "Fine payment from member #{$fine->member_id}",
                date: now()->toDateString(),
                lines: [
                    new JournalLineDTO('1000', debit: (float) $fine->amount),
                    new JournalLineDTO('4100', credit: (float) $fine->amount),
                ],
                sourceType: Fine::class,
                sourceId: $fine->id,
                recordedBy: $recordedBy ?? $fine->recorded_by,
            ));

            return $fine->fresh();
        });
    }
}
