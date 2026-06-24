<?php

declare(strict_types=1);

namespace App\Features\Welfare\Services;

use App\DTOs\JournalEntryDTO;
use App\DTOs\JournalLineDTO;
use App\Features\Ledger\Services\LedgerService;
use App\Features\Welfare\Models\WelfareContribution;
use App\Features\Welfare\Models\WelfareDisbursement;
use Illuminate\Support\Facades\DB;

/**
 * Domain service for Welfare.
 */
class WelfareService
{
    /**
     * Create a new instance.
     */
    public function __construct(private LedgerService $ledgerService) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordContribution(array $data): WelfareContribution
    {
        return DB::transaction(function () use ($data): WelfareContribution {
            $contribution = WelfareContribution::create($data);

            $this->ledgerService->post(new JournalEntryDTO(
                description: "Welfare contribution from member #{$contribution->member_id}",
                date: $contribution->date->toDateString(),
                lines: [
                    new JournalLineDTO('1000', debit: (float) $contribution->amount),
                    new JournalLineDTO('3000', credit: (float) $contribution->amount),
                ],
                sourceType: WelfareContribution::class,
                sourceId: $contribution->id,
                recordedBy: $contribution->recorded_by,
            ));

            return $contribution->load('member');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordDisbursement(array $data): WelfareDisbursement
    {
        return DB::transaction(function () use ($data): WelfareDisbursement {
            $disbursement = WelfareDisbursement::create($data);

            $this->ledgerService->post(new JournalEntryDTO(
                description: "Welfare disbursement to member #{$disbursement->member_id}",
                date: $disbursement->date->toDateString(),
                lines: [
                    new JournalLineDTO('3000', debit: (float) $disbursement->amount),
                    new JournalLineDTO('1000', credit: (float) $disbursement->amount),
                ],
                sourceType: WelfareDisbursement::class,
                sourceId: $disbursement->id,
                recordedBy: $disbursement->recorded_by,
            ));

            return $disbursement->load('member');
        });
    }
}
