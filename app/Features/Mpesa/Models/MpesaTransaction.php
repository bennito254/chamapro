<?php

namespace App\Features\Mpesa\Models;

use App\Features\Members\Models\Member;
use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use Database\Factories\MpesaTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'group_id', 'member_id', 'transaction_id', 'phone_number', 'amount', 'type',
    'status', 'reference', 'payable_type', 'payable_id', 'metadata',
])]
/**
 * Eloquent model for mpesa transaction.
 */
class MpesaTransaction extends Model
{
    /** @use HasFactory<MpesaTransactionFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /**
     * Member.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Payable.
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }
}
