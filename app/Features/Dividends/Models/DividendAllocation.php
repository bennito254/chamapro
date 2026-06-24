<?php

namespace App\Features\Dividends\Models;

use App\Features\Members\Models\Member;
use App\Models\Concerns\HasSqid;
use Database\Factories\DividendAllocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'dividend_run_id', 'member_id', 'member_contributions', 'ownership_percentage',
    'dividend_amount',
])]
/**
 * Eloquent model for dividend allocation.
 */
class DividendAllocation extends Model
{
    /** @use HasFactory<DividendAllocationFactory> */
    use HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'member_contributions' => 'decimal:2',
            'ownership_percentage' => 'decimal:4',
            'dividend_amount' => 'decimal:2',
        ];
    }

    /**
     * Dividend run.
     */
    public function dividendRun(): BelongsTo
    {
        return $this->belongsTo(DividendRun::class);
    }

    /**
     * Member.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
