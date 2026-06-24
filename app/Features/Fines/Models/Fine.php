<?php

namespace App\Features\Fines\Models;

use App\Features\Members\Models\Member;
use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use App\Models\User;
use Database\Factories\FineFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'group_id', 'member_id', 'fine_type_id', 'amount', 'reason', 'notes',
    'date', 'is_paid', 'paid_at', 'recorded_by',
])]
/**
 * Eloquent model for fine.
 */
class Fine extends Model
{
    /** @use HasFactory<FineFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
            'is_paid' => 'boolean',
            'paid_at' => 'date',
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
     * Fine type.
     */
    public function fineType(): BelongsTo
    {
        return $this->belongsTo(FineType::class);
    }

    /**
     * Recorded by.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
