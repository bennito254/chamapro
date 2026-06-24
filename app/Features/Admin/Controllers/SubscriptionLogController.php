<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Services\GroupOwnerService;
use App\Features\Groups\Models\Group;
use App\Features\Subscriptions\Models\Subscription;
use App\Features\Subscriptions\Models\SubscriptionPayment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for group subscription activity logs.
 */
class SubscriptionLogController extends Controller
{
    public function __construct(
        private GroupOwnerService $groupOwnerService,
    ) {}

    /**
     * Subscription history and payment logs across all groups.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString();

        $groups = Group::query()
            ->with(['activeSubscription.plan'])
            ->withCount(['subscriptions', 'subscriptionPayments'])
            ->when(filled($search), fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(function (Group $group): array {
                $subscriptions = Subscription::query()
                    ->where('group_id', $group->id)
                    ->with('plan')
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(fn (Subscription $subscription): array => [
                        'sqid' => $subscription->sqid,
                        'status' => $subscription->status->value,
                        'start_date' => $subscription->start_date?->toDateString(),
                        'end_date' => $subscription->end_date?->toDateString(),
                        'plan' => $subscription->plan?->name,
                    ]);

                $payments = SubscriptionPayment::query()
                    ->where('group_id', $group->id)
                    ->with('plan')
                    ->latest()
                    ->limit(5)
                    ->get()
                    ->map(fn (SubscriptionPayment $payment): array => [
                        'sqid' => $payment->sqid,
                        'status' => $payment->status->value,
                        'amount' => $payment->amount,
                        'phone_number' => $payment->phone_number,
                        'mpesa_receipt_number' => $payment->mpesa_receipt_number,
                        'paid_at' => $payment->paid_at?->toIso8601String(),
                        'plan' => $payment->plan?->name,
                    ]);

                return [
                    'sqid' => $group->sqid,
                    'name' => $group->name,
                    'status' => $group->status,
                    'owner' => $this->groupOwnerService->resolve($group),
                    'subscriptions_count' => $group->subscriptions_count,
                    'payments_count' => $group->subscription_payments_count,
                    'active_subscription' => $group->activeSubscription ? [
                        'status' => $group->activeSubscription->status->value,
                        'end_date' => $group->activeSubscription->end_date?->toDateString(),
                        'plan' => $group->activeSubscription->plan?->name,
                    ] : null,
                    'recent_subscriptions' => $subscriptions,
                    'recent_payments' => $payments,
                ];
            });

        return Inertia::render('admin/subscription-logs/index', [
            'groups' => $groups,
            'filters' => ['search' => $search],
        ]);
    }
}
