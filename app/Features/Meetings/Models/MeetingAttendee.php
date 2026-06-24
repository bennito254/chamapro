<?php

namespace App\Features\Meetings\Models;

use App\Features\Members\Models\Member;
use App\Models\Concerns\HasSqid;
use Database\Factories\MeetingAttendeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'meeting_id', 'member_id', 'status', 'notes',
])]
/**
 * Eloquent model for meeting attendee.
 */
class MeetingAttendee extends Model
{
    /** @use HasFactory<MeetingAttendeeFactory> */
    use HasFactory, HasSqid;

    /**
     * Meeting.
     */
    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * Member.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
