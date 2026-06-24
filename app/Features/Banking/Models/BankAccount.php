<?php

namespace App\Features\Banking\Models;

use App\Features\Ledger\Models\ChartOfAccount;
use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use Database\Factories\BankAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_id', 'bank_name', 'branch', 'account_name', 'account_number',
    'opening_balance', 'current_balance', 'chart_of_account_id', 'status',
])]
/**
 * Eloquent model for bank account.
 */
class BankAccount extends Model
{
    /** @use HasFactory<BankAccountFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
        ];
    }

    /**
     * Chart of account.
     */
    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    /**
     * Transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    /**
     * Incoming transfers.
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(BankTransaction::class, 'destination_bank_account_id');
    }
}
