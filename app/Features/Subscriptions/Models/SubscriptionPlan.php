<?php

namespace App\Features\Subscriptions\Models;

use App\Enums\BillingCycle;
use App\Models\Concerns\HasSqid;
use Database\Factories\SubscriptionPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'billing_cycle', 'amount', 'discount_percentage',
    'max_members', 'max_users', 'trial_days', 'status',
])]
/**
 * Eloquent model for subscription plan.
 */
class SubscriptionPlan extends Model
{
    /** @use HasFactory<SubscriptionPlanFactory> */
    use HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'billing_cycle' => BillingCycle::class,
            'amount' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
        ];
    }

    /**
     * Subscriptions.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
