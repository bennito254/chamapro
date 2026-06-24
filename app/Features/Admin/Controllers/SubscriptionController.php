<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Requests\UpdateSubscriptionRequest;
use App\Features\Admin\Services\GroupOwnerService;
use App\Features\Subscriptions\Models\Subscription;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Subscription.
 */
class SubscriptionController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(private GroupOwnerService $groupOwnerService) {}

    /**
     * List subscriptions with group owner contact details.
     */
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();

        $subscriptions = Subscription::query()
            ->with(['group', 'plan'])
            ->when(filled($status) && $status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Subscription $subscription): array => [
                'id' => $subscription->id,
                'sqid' => $subscription->sqid,
                'status' => $subscription->status->value,
                'start_date' => $subscription->start_date?->toDateString(),
                'end_date' => $subscription->end_date?->toDateString(),
                'group' => $subscription->group ? [
                    'sqid' => $subscription->group->sqid,
                    'name' => $subscription->group->name,
                ] : null,
                'plan' => $subscription->plan ? [
                    'sqid' => $subscription->plan->sqid,
                    'name' => $subscription->plan->name,
                    'amount' => $subscription->plan->amount,
                ] : null,
                'owner' => $this->groupOwnerService->resolve($subscription->group),
            ]);

        return Inertia::render('admin/subscriptions/index', [
            'subscriptions' => $subscriptions,
            'filters' => ['status' => $status ?: 'all'],
            'statusOptions' => [
                ['value' => 'all', 'label' => 'All statuses'],
                ['value' => 'trial', 'label' => 'Trial'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'expired', 'label' => 'Expired'],
                ['value' => 'suspended', 'label' => 'Suspended'],
            ],
        ]);
    }

    /**
     * Show the edit form.
     */
    public function edit(Subscription $subscription): Response
    {
        $subscription->load(['group', 'plan']);

        return Inertia::render('admin/subscriptions/edit', [
            'subscription' => $subscription,
            'owner' => $this->groupOwnerService->resolve($subscription->group),
            'plans' => SubscriptionPlan::query()->orderBy('name')->get(['id', 'name', 'amount', 'status']),
        ]);
    }

    /**
     * Update a subscription.
     */
    public function update(UpdateSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $subscription->update($request->validated());

        return redirect()
            ->route('admin.subscriptions.index')
            ->with('success', 'Subscription updated successfully.');
    }
}
