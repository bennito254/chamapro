<?php

namespace App\Features\Subscriptions\Models;

use App\Enums\SubscriptionStatus;
use App\Features\Groups\Models\Group;
use App\Models\Concerns\HasSqid;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'group_id', 'subscription_plan_id', 'start_date', 'end_date', 'status',
])]
/**
 * Eloquent model for subscription.
 */
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => SubscriptionStatus::class,
        ];
    }

    /**
     * Group.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Is writable.
     */
    public function isWritable(): bool
    {
        return $this->status->isWritable() && $this->end_date->isFuture();
    }
}
