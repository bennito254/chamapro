<?php

namespace App\Features\Support\Models;

use App\Models\Concerns\HasSqid;
use Database\Factories\SupportTicketNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'support_ticket_id', 'author_type', 'author_id', 'body', 'is_internal',
])]
/**
 * Eloquent model for support ticket note.
 */
class SupportTicketNote extends Model
{
    /** @use HasFactory<SupportTicketNoteFactory> */
    use HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    /**
     * Support ticket.
     */
    public function supportTicket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class);
    }

    /**
     * Author.
     */
    public function author(): MorphTo
    {
        return $this->morphTo();
    }
}
