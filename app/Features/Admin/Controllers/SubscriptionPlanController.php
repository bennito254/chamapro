<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Requests\StoreSubscriptionPlanRequest;
use App\Features\Admin\Requests\UpdateSubscriptionPlanRequest;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Subscription Plan.
 */
class SubscriptionPlanController extends Controller
{
    /**
     * Index.
     */
    public function index(): Response
    {
        $plans = SubscriptionPlan::query()
            ->withCount('subscriptions')
            ->latest()
            ->paginate(15);

        return Inertia::render('admin/plans/index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Create.
     */
    public function create(): Response
    {
        return Inertia::render('admin/plans/create');
    }

    /**
     * Store.
     */
    public function store(StoreSubscriptionPlanRequest $request): RedirectResponse
    {
        SubscriptionPlan::create($request->validated());

        return redirect()->route('admin.plans.index')
            ->with('success', 'Subscription plan created successfully.');
    }

    /**
     * Edit.
     */
    public function edit(SubscriptionPlan $plan): Response
    {
        return Inertia::render('admin/plans/edit', [
            'plan' => $plan,
        ]);
    }

    /**
     * Update.
     */
    public function update(UpdateSubscriptionPlanRequest $request, SubscriptionPlan $plan): RedirectResponse
    {
        $plan->update($request->validated());

        return redirect()->route('admin.plans.index')
            ->with('success', 'Subscription plan updated successfully.');
    }

    /**
     * Destroy.
     */
    public function destroy(SubscriptionPlan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            return back()->withErrors([
                'plan' => 'Cannot delete a plan that has subscriptions. Deactivate it instead.',
            ]);
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Subscription plan deleted successfully.');
    }
}
