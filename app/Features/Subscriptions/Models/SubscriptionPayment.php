<?php

namespace App\Features\Subscriptions\Models;

use App\Enums\SubscriptionPaymentStatus;
use App\Features\Groups\Models\Group;
use App\Features\Mpesa\Models\MpesaTransaction;
use App\Models\Concerns\HasSqid;
use Database\Factories\SubscriptionPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'group_id', 'subscription_plan_id', 'mpesa_transaction_id', 'subscription_id',
    'phone_number', 'amount', 'status', 'checkout_request_id', 'mpesa_receipt_number',
    'paid_at', 'metadata',
])]
/**
 * Eloquent model for subscription payment.
 */
class SubscriptionPayment extends Model
{
    /** @use HasFactory<SubscriptionPaymentFactory> */
    use HasFactory, HasSqid;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => SubscriptionPaymentStatus::class,
            'paid_at' => 'datetime',
            'metadata' => 'array',
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
     * Mpesa transaction.
     */
    public function mpesaTransaction(): BelongsTo
    {
        return $this->belongsTo(MpesaTransaction::class);
    }

    /**
     * Subscription created after successful payment.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
