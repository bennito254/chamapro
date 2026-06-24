<?php

namespace App\Features\Ledger\Models;

use App\Enums\AccountType;
use App\Features\Banking\Models\BankAccount;
use App\Features\Banking\Models\CashAccount;
use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use Database\Factories\ChartOfAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_id', 'code', 'name', 'type', 'balance', 'is_system', 'status',
])]
/**
 * Eloquent model for chart of account.
 */
class ChartOfAccount extends Model
{
    /** @use HasFactory<ChartOfAccountFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'balance' => 'decimal:2',
            'is_system' => 'boolean',
        ];
    }

    /**
     * Journal entry lines.
     */
    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Bank accounts.
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Cash accounts.
     */
    public function cashAccounts(): HasMany
    {
        return $this->hasMany(CashAccount::class);
    }
}
