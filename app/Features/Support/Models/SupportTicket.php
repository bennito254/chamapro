<?php

namespace App\Features\Support\Models;

use App\Enums\TicketStatus;
use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use App\Models\User;
use Database\Factories\SupportTicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_id', 'user_id', 'subject', 'description', 'status', 'priority',
])]
/**
 * Eloquent model for support ticket.
 */
class SupportTicket extends Model
{
    /** @use HasFactory<SupportTicketFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
        ];
    }

    /**
     * User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Notes.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(SupportTicketNote::class);
    }
}
