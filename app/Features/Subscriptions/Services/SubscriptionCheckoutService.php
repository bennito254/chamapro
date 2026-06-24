<?php

declare(strict_types=1);

namespace App\Features\Subscriptions\Services;

use App\Enums\SubscriptionPaymentStatus;
use App\Features\Admin\Services\PlatformMpesaSettingsService;
use App\Features\Groups\Models\Group;
use App\Features\Mpesa\Models\MpesaTransaction;
use App\Features\Mpesa\Services\MpesaDarajaClient;
use App\Features\Mpesa\Services\MpesaTransactionMatcher;
use App\Features\Subscriptions\Models\Subscription;
use App\Features\Subscriptions\Models\SubscriptionPayment;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Handles M-Pesa Express checkout for subscription renewals.
 */
class SubscriptionCheckoutService
{
    public function __construct(
        private PlatformMpesaSettingsService $mpesaSettings,
        private MpesaDarajaClient $darajaClient,
        private MpesaTransactionMatcher $matcher,
        private SubscriptionService $subscriptionService,
    ) {}

    public function initiate(Group $group, SubscriptionPlan $plan, string $phoneNumber): SubscriptionPayment
    {
        $normalizedPhone = $this->matcher->normalizePhone($phoneNumber);

        if ($normalizedPhone === '') {
            throw new RuntimeException('A valid M-Pesa phone number is required.');
        }

        return DB::transaction(function () use ($group, $plan, $normalizedPhone): SubscriptionPayment {
            $payment = SubscriptionPayment::create([
                'group_id' => $group->id,
                'subscription_plan_id' => $plan->id,
                'phone_number' => $normalizedPhone,
                'amount' => $plan->amount,
                'status' => SubscriptionPaymentStatus::Pending,
            ]);

            if ($this->mpesaSettings->usesStubMode()) {
                return $this->initiateStubCheckout($payment, $group, $plan);
            }

            $stk = $this->darajaClient->stkPush(
                $normalizedPhone,
                (float) $plan->amount,
                'SUB-'.$payment->id,
                'ChamaPro '.$plan->name,
            );

            $transaction = MpesaTransaction::withoutGlobalScopes()->create([
                'group_id' => $group->id,
                'transaction_id' => $stk['checkout_request_id'],
                'phone_number' => $normalizedPhone,
                'amount' => $plan->amount,
                'type' => 'subscription_renewal',
                'status' => 'pending',
                'reference' => 'SUB-'.$payment->id,
                'payable_type' => SubscriptionPayment::class,
                'payable_id' => $payment->id,
                'metadata' => [
                    'stub' => false,
                    'merchant_request_id' => $stk['merchant_request_id'],
                    'daraja_response' => $stk['response'],
                    'initiated_at' => now()->toIso8601String(),
                ],
            ]);

            $payment->update([
                'mpesa_transaction_id' => $transaction->id,
                'checkout_request_id' => $stk['checkout_request_id'],
            ]);

            return $payment->fresh(['plan', 'mpesaTransaction']);
        });
    }

    public function completeFromCallback(MpesaTransaction $transaction, array $callbackPayload): ?SubscriptionPayment
    {
        if ($transaction->payable_type !== SubscriptionPayment::class || ! $transaction->payable_id) {
            return null;
        }

        $payment = SubscriptionPayment::query()->find($transaction->payable_id);

        if (! $payment || $payment->status === SubscriptionPaymentStatus::Completed) {
            return $payment;
        }

        $resultCode = data_get($callbackPayload, 'Body.stkCallback.ResultCode')
            ?? data_get($callbackPayload, 'ResultCode');

        if ((int) $resultCode !== 0) {
            $payment->update([
                'status' => SubscriptionPaymentStatus::Failed,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'callback' => $callbackPayload,
                    'failed_at' => now()->toIso8601String(),
                ]),
            ]);

            return $payment->fresh();
        }

        return $this->markCompleted($payment, $transaction, $callbackPayload);
    }

    /**
     * @param  array<string, mixed>  $callbackPayload
     */
    public function markCompleted(
        SubscriptionPayment $payment,
        ?MpesaTransaction $transaction = null,
        array $callbackPayload = [],
    ): SubscriptionPayment {
        if ($payment->status === SubscriptionPaymentStatus::Completed) {
            return $payment;
        }

        return DB::transaction(function () use ($payment, $transaction, $callbackPayload): SubscriptionPayment {
            $group = $payment->group ?? Group::query()->findOrFail($payment->group_id);
            $plan = $payment->plan ?? SubscriptionPlan::query()->findOrFail($payment->subscription_plan_id);

            $subscription = $this->subscriptionService->renew($group, $plan);

            $receiptNumber = data_get($callbackPayload, 'Body.stkCallback.CallbackMetadata.Item')
                ? $this->extractCallbackMetadata($callbackPayload, 'MpesaReceiptNumber')
                : data_get($callbackPayload, 'MpesaReceiptNumber');

            $payment->update([
                'status' => SubscriptionPaymentStatus::Completed,
                'subscription_id' => $subscription->id,
                'mpesa_receipt_number' => is_string($receiptNumber) ? $receiptNumber : $payment->mpesa_receipt_number,
                'paid_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'callback' => $callbackPayload ?: null,
                    'completed_at' => now()->toIso8601String(),
                ]),
            ]);

            if ($transaction) {
                $transaction->update([
                    'status' => 'completed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'callback' => $callbackPayload ?: null,
                        'processed_at' => now()->toIso8601String(),
                    ]),
                ]);
            }

            return $payment->fresh(['subscription', 'plan', 'group']);
        });
    }

    private function initiateStubCheckout(
        SubscriptionPayment $payment,
        Group $group,
        SubscriptionPlan $plan,
    ): SubscriptionPayment {
        $checkoutRequestId = 'CHK'.Str::upper(Str::random(12));

        $transaction = MpesaTransaction::withoutGlobalScopes()->create([
            'group_id' => $group->id,
            'transaction_id' => $checkoutRequestId,
            'phone_number' => $payment->phone_number,
            'amount' => $plan->amount,
            'type' => 'subscription_renewal',
            'status' => 'pending',
            'reference' => 'SUB-'.$payment->id,
            'payable_type' => SubscriptionPayment::class,
            'payable_id' => $payment->id,
            'metadata' => [
                'stub' => true,
                'initiated_at' => now()->toIso8601String(),
            ],
        ]);

        $payment->update([
            'mpesa_transaction_id' => $transaction->id,
            'checkout_request_id' => $checkoutRequestId,
            'metadata' => ['stub' => true],
        ]);

        $this->markCompleted($payment->fresh(), $transaction, [
            'ResultCode' => 0,
            'MpesaReceiptNumber' => 'STUB'.Str::upper(Str::random(8)),
            'stub' => true,
        ]);

        return $payment->fresh(['subscription', 'plan', 'mpesaTransaction']);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractCallbackMetadata(array $payload, string $name): ?string
    {
        $items = data_get($payload, 'Body.stkCallback.CallbackMetadata.Item', []);

        if (! is_array($items)) {
            return null;
        }

        foreach ($items as $item) {
            if (data_get($item, 'Name') === $name) {
                $value = data_get($item, 'Value');

                return is_scalar($value) ? (string) $value : null;
            }
        }

        return null;
    }
}
