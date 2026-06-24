<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Requests\StoreGroupRequest;
use App\Features\Admin\Requests\UpdateGroupRequest;
use App\Features\Admin\Services\GroupOwnerService;
use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Group.
 */
class GroupController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private GroupProvisioningService $provisioningService,
        private GroupOwnerService $groupOwnerService,
    ) {}

    /**
     * Index.
     */
    public function index(): Response
    {
        $groups = Group::query()
            ->with('activeSubscription.plan')
            ->withCount('members', 'users')
            ->latest()
            ->paginate(15);

        return Inertia::render('admin/groups/index', [
            'groups' => $groups,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        return Inertia::render('admin/groups/create', [
            'plans' => SubscriptionPlan::where('status', 'active')->get(),
        ]);
    }

    /**
     * Store.
     */
    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $plan = SubscriptionPlan::findOrFail($request->validated('subscription_plan_id'));
        $group = $this->provisioningService->provision($request->safe()->except('subscription_plan_id'), $plan);

        return redirect()->route('admin.groups.show', $group)
            ->with('success', 'Group created successfully.');
    }

    /**
     * Show.
     */
    public function show(Group $group): Response
    {
        $group->load(['activeSubscription.plan', 'members', 'users']);

        return Inertia::render('admin/groups/show', [
            'group' => $group,
            'owner' => $this->groupOwnerService->resolve($group),
        ]);
    }

    /**
     * Edit.
     */
    public function edit(Group $group): Response
    {
        return Inertia::render('admin/groups/edit', [
            'group' => $group,
            'plans' => SubscriptionPlan::where('status', 'active')->get(),
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateGroupRequest $request, Group $group): RedirectResponse
    {
        $group->update($request->validated());

        return redirect()->route('admin.groups.show', $group)
            ->with('success', 'Group updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(Group $group): RedirectResponse
    {
        $group->delete();

        return redirect()->route('admin.groups.index')
            ->with('success', 'Group deleted successfully.');
    }

    /**
     * Suspend.
     */
    public function suspend(Group $group): RedirectResponse
    {
        $group->update(['status' => 'suspended']);

        $group->activeSubscription?->update(['status' => 'suspended']);

        return back()->with('success', 'Group suspended successfully.');
    }

    /**
     * Activate.
     */
    public function activate(Group $group): RedirectResponse
    {
        $group->update(['status' => 'active']);

        $subscription = $group->activeSubscription;

        if ($subscription && $subscription->end_date->isFuture()) {
            $subscription->update(['status' => 'active']);
        }

        return back()->with('success', 'Group activated successfully.');
    }

    /**
     * Extend subscription.
     */
    public function extendSubscription(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $subscription = $group->activeSubscription;

        if (! $subscription) {
            return back()->withErrors(['subscription' => 'No active subscription found for this group.']);
        }

        $days = (int) $validated['days'];

        $subscription->update([
            'end_date' => $subscription->end_date->addDays($days),
            'status' => 'active',
        ]);

        return back()->with('success', 'Subscription extended successfully.');
    }
}
