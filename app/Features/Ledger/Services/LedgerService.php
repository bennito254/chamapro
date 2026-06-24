<?php

namespace App\Features\Ledger\Services;

use App\DTOs\JournalEntryDTO;
use App\Features\Ledger\Models\ChartOfAccount;
use App\Features\Ledger\Models\JournalEntry;
use App\Features\Ledger\Models\JournalEntryLine;
use App\Support\GroupContext;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Domain service for Ledger.
 */
class LedgerService
{
    /**
     * Create a new instance.
     */
    public function __construct(private GroupContext $groupContext) {}

    /**
     * Post.
     */
    public function post(JournalEntryDTO $dto): JournalEntry
    {
        $totalDebit = collect($dto->lines)->sum(fn ($line) => $line->debit);
        $totalCredit = collect($dto->lines)->sum(fn ($line) => $line->credit);

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw new InvalidArgumentException('Journal entry debits must equal credits.');
        }

        return DB::transaction(function () use ($dto) {
            $entry = JournalEntry::create([
                'group_id' => $this->groupContext->id(),
                'source_type' => $dto->sourceType,
                'source_id' => $dto->sourceId,
                'date' => $dto->date,
                'description' => $dto->description,
                'recorded_by' => $dto->recordedBy,
            ]);

            foreach ($dto->lines as $line) {
                $account = ChartOfAccount::where('code', $line->accountCode)->firstOrFail();

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $account->id,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                ]);

                if ($line->debit > 0) {
                    $account->increment('balance', $line->debit);
                }
                if ($line->credit > 0) {
                    $account->decrement('balance', $line->credit);
                }
            }

            return $entry->load('lines.chartOfAccount');
        });
    }
}
