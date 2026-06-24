<?php

namespace App\Features\Banking\Models;

use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use App\Models\User;
use Database\Factories\BankTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'group_id', 'bank_account_id', 'type', 'amount', 'date', 'reference',
    'notes', 'destination_bank_account_id', 'recorded_by',
])]
/**
 * Eloquent model for bank transaction.
 */
class BankTransaction extends Model
{
    /** @use HasFactory<BankTransactionFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    /**
     * Bank account.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * Destination bank account.
     */
    public function destinationBankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'destination_bank_account_id');
    }

    /**
     * Recorded by.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
