<?php

namespace App\Features\Admin\Controllers;

use App\Enums\SubscriptionPaymentStatus;
use App\Features\Subscriptions\Models\SubscriptionPayment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for subscription payment records.
 */
class SubscriptionPaymentController extends Controller
{
    /**
     * List subscription payments with optional status filter.
     */
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();

        $payments = SubscriptionPayment::query()
            ->with(['group', 'plan', 'subscription', 'mpesaTransaction'])
            ->when(filled($status) && $status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (SubscriptionPayment $payment): array => [
                'sqid' => $payment->sqid,
                'status' => $payment->status->value,
                'amount' => $payment->amount,
                'phone_number' => $payment->phone_number,
                'mpesa_receipt_number' => $payment->mpesa_receipt_number,
                'checkout_request_id' => $payment->checkout_request_id,
                'paid_at' => $payment->paid_at?->toIso8601String(),
                'created_at' => $payment->created_at?->toIso8601String(),
                'group' => $payment->group ? [
                    'sqid' => $payment->group->sqid,
                    'name' => $payment->group->name,
                ] : null,
                'plan' => $payment->plan ? [
                    'sqid' => $payment->plan->sqid,
                    'name' => $payment->plan->name,
                ] : null,
            ]);

        return Inertia::render('admin/subscription-payments/index', [
            'payments' => $payments,
            'filters' => ['status' => $status ?: 'all'],
            'statusOptions' => [
                ['value' => 'all', 'label' => 'All statuses'],
                ...collect(SubscriptionPaymentStatus::cases())->map(fn (SubscriptionPaymentStatus $case) => [
                    'value' => $case->value,
                    'label' => ucfirst($case->value),
                ]),
            ],
            'stats' => [
                'total' => SubscriptionPayment::count(),
                'completed' => SubscriptionPayment::where('status', SubscriptionPaymentStatus::Completed)->count(),
                'pending' => SubscriptionPayment::where('status', SubscriptionPaymentStatus::Pending)->count(),
                'failed' => SubscriptionPayment::where('status', SubscriptionPaymentStatus::Failed)->count(),
            ],
        ]);
    }
}
