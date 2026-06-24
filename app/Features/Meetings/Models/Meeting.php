<?php

namespace App\Features\Meetings\Models;

use App\Models\Concerns\BelongsToGroup;
use App\Models\Concerns\HasSqid;
use App\Models\User;
use Database\Factories\MeetingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'group_id', 'title', 'date', 'time', 'venue', 'agenda', 'minutes',
    'minutes_file', 'status', 'created_by',
])]
/**
 * Eloquent model for meeting.
 */
class Meeting extends Model
{
    /** @use HasFactory<MeetingFactory> */
    use BelongsToGroup, HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'date' => 'date',
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
     * Attendees.
     */
    public function attendees(): HasMany
    {
        return $this->hasMany(MeetingAttendee::class);
    }
}
