<?php

namespace App\Features\Ledger\Models;

use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use App\Models\User;
use Database\Factories\JournalEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'group_id', 'source_type', 'source_id', 'date', 'description', 'recorded_by',
])]
/**
 * Eloquent model for journal entry.
 */
class JournalEntry extends Model
{
    /** @use HasFactory<JournalEntryFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Source.
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Lines.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    /**
     * Recorded by.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
