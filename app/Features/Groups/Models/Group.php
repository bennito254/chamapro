<?php

namespace App\Features\Groups\Models;

use App\Enums\SubscriptionStatus;
use App\Features\Members\Models\Member;
use App\Features\Subscriptions\Models\Subscription;
use App\Features\Subscriptions\Models\SubscriptionPayment;
use App\Models\Concerns\HasSqid;
use App\Models\User;
use Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'name', 'registration_number', 'phone', 'email', 'address', 'county',
    'constituency', 'logo', 'meeting_day', 'meeting_frequency', 'currency',
    'status', 'mpesa_settings',
])]
/**
 * Eloquent model for group.
 */
class Group extends Model
{
    /** @use HasFactory<GroupFactory> */
    use HasFactory, HasSqid;

    protected static function newFactory(): GroupFactory
    {
        return GroupFactory::new();
    }

    protected function casts(): array
    {
        return [
            'mpesa_settings' => 'array',
        ];
    }

    /**
     * Users.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Members.
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    /**
     * Subscriptions.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Subscription payments.
     */
    public function subscriptionPayments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    /**
     * Active subscription.
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', [
                SubscriptionStatus::Trial->value,
                SubscriptionStatus::Active->value,
                SubscriptionStatus::Expired->value,
            ])
            ->latestOfMany();
    }
}
