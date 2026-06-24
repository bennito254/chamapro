<?php

namespace App\Features\Contributions\Models;

use App\Features\Members\Models\Member;
use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use App\Models\User;
use Database\Factories\ContributionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'group_id', 'member_id', 'contribution_type_id', 'contribution_channel_id',
    'amount', 'transaction_reference', 'date', 'notes', 'recorded_by',
])]
/**
 * Eloquent model for contribution.
 */
class Contribution extends Model
{
    /** @use HasFactory<ContributionFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
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
     * Contribution type.
     */
    public function contributionType(): BelongsTo
    {
        return $this->belongsTo(ContributionType::class);
    }

    /**
     * Contribution channel.
     */
    public function contributionChannel(): BelongsTo
    {
        return $this->belongsTo(ContributionChannel::class);
    }

    /**
     * Recorded by.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
