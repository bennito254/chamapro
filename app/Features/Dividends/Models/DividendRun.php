<?php

namespace App\Features\Dividends\Models;

use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use App\Models\User;
use Database\Factories\DividendRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_id', 'year', 'total_profit', 'formula', 'status', 'created_by',
])]
/**
 * Eloquent model for dividend run.
 */
class DividendRun extends Model
{
    /** @use HasFactory<DividendRunFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'total_profit' => 'decimal:2',
            'formula' => 'array',
        ];
    }

    /**
     * Created by.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Allocations.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(DividendAllocation::class);
    }
}
