<?php

namespace App\Features\Subscriptions\Controllers;

use App\Features\Admin\Services\PlatformMpesaSettingsService;
use App\Features\Subscriptions\Models\SubscriptionPayment;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Features\Subscriptions\Requests\RenewSubscriptionRequest;
use App\Features\Subscriptions\Services\SubscriptionCheckoutService;
use App\Http\Controllers\Controller;
use App\Support\GroupContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Subscription Renewal.
 */
class SubscriptionRenewalController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private SubscriptionCheckoutService $checkoutService,
        private PlatformMpesaSettingsService $mpesaSettings,
        private GroupContext $groupContext,
    ) {}

    /**
     * Show.
     */
    public function show(): Response
    {
        $group = $this->groupContext->get();

        return Inertia::render('portal/subscription/renew', [
            'group' => $group?->load('activeSubscription.plan'),
            'plans' => SubscriptionPlan::where('status', 'active')->get(),
            'mpesa' => [
                'stk_enabled' => $this->mpesaSettings->isStkEnabled(),
                'stub_mode' => $this->mpesaSettings->usesStubMode(),
                'default_phone' => $group?->phone,
            ],
        ]);
    }

    /**
     * Initiate M-Pesa Express checkout for subscription renewal.
     */
    public function renew(RenewSubscriptionRequest $request): RedirectResponse
    {
        $group = $this->groupContext->get();

        if (! $group) {
            return back()->withErrors(['group' => 'No group context found.']);
        }

        $plan = SubscriptionPlan::findOrFail($request->validated('subscription_plan_id'));

        $payment = $this->checkoutService->initiate(
            $group,
            $plan,
            $request->validated('phone_number'),
        );

        if ($payment->status->value === 'completed') {
            return redirect()->route('portal.dashboard')
                ->with('success', 'Subscription renewed successfully. Receipt: '.($payment->mpesa_receipt_number ?? 'N/A'));
        }

        return redirect()
            ->route('portal.subscription.renew.status', $payment)
            ->with('success', 'M-Pesa payment request sent. Check your phone to complete payment.');
    }

    /**
     * Poll payment status after STK push.
     */
    public function status(SubscriptionPayment $payment): Response|RedirectResponse
    {
        $group = $this->groupContext->get();

        if (! $group || $payment->group_id !== $group->id) {
            abort(404);
        }

        $payment->load(['plan', 'mpesaTransaction']);

        if ($payment->status->value === 'completed') {
            return redirect()->route('portal.dashboard')
                ->with('success', 'Subscription renewed successfully.');
        }

        return Inertia::render('portal/subscription/status', [
            'payment' => [
                'sqid' => $payment->sqid,
                'status' => $payment->status->value,
                'amount' => $payment->amount,
                'phone_number' => $payment->phone_number,
                'mpesa_receipt_number' => $payment->mpesa_receipt_number,
                'plan' => $payment->plan ? [
                    'name' => $payment->plan->name,
                    'amount' => $payment->plan->amount,
                ] : null,
            ],
        ]);
    }
}
