<?php

declare(strict_types=1);

namespace App\Features\Mpesa\Services;

use App\Features\Groups\Models\Group;
use App\Features\Mpesa\Models\MpesaCallbackLog;
use App\Features\Mpesa\Models\MpesaTransaction;
use App\Features\Subscriptions\Services\SubscriptionCheckoutService;
use App\Support\GroupContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Domain service for Mpesa.
 */
class MpesaService
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private GroupContext $groupContext,
        private MpesaTransactionMatcher $matcher,
        private SubscriptionCheckoutService $subscriptionCheckout,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function initiateStkPush(array $data): MpesaTransaction
    {
        $group = $this->groupContext->get();
        $transactionId = (string) ($data['transaction_id'] ?? 'CHK'.Str::upper(Str::random(12)));

        return MpesaTransaction::create([
            'group_id' => $this->groupContext->id(),
            'member_id' => $data['member_id'] ?? $this->matcher->matchByPhone($data['phone_number'] ?? '')?->id,
            'transaction_id' => $transactionId,
            'phone_number' => $data['phone_number'],
            'amount' => $data['amount'],
            'type' => 'stk_push',
            'status' => 'pending',
            'reference' => $data['reference'] ?? null,
            'payable_type' => $data['payable_type'] ?? null,
            'payable_id' => $data['payable_id'] ?? null,
            'metadata' => [
                'stub' => true,
                'initiated_at' => now()->toIso8601String(),
                'mpesa_settings' => $group instanceof Group ? $group->mpesa_settings : null,
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function processCallback(array $payload): MpesaCallbackLog
    {
        return DB::transaction(function () use ($payload): MpesaCallbackLog {
            $transactionId = $this->extractTransactionId($payload);

            $transaction = $transactionId
                ? MpesaTransaction::withoutGlobalScopes()->where('transaction_id', $transactionId)->first()
                : null;

            $log = MpesaCallbackLog::create([
                'group_id' => $transaction?->group_id,
                'transaction_id' => $transactionId,
                'payload' => $payload,
                'processed' => false,
            ]);

            if (! $transaction) {
                return $log;
            }

            $resultCode = data_get($payload, 'Body.stkCallback.ResultCode')
                ?? data_get($payload, 'ResultCode');

            $isSuccess = (int) $resultCode === 0;

            if ($transaction->type === 'subscription_renewal') {
                if ($isSuccess) {
                    $this->subscriptionCheckout->completeFromCallback($transaction, $payload);
                } else {
                    $transaction->update([
                        'status' => 'failed',
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'callback' => $payload,
                            'processed_at' => now()->toIso8601String(),
                        ]),
                    ]);
                }

                $log->update(['processed' => true]);

                return $log;
            }

            $transaction->update([
                'status' => $isSuccess ? 'completed' : 'failed',
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'callback' => $payload,
                    'processed_at' => now()->toIso8601String(),
                ]),
            ]);

            if (! $transaction->member_id) {
                $member = $this->matcher->matchByPhone($transaction->phone_number);

                if ($member) {
                    $transaction->update(['member_id' => $member->id]);
                }
            }

            $log->update(['processed' => true]);

            return $log;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractTransactionId(array $payload): ?string
    {
        $candidates = [
            data_get($payload, 'Body.stkCallback.CheckoutRequestID'),
            data_get($payload, 'CheckoutRequestID'),
            data_get($payload, 'transaction_id'),
            data_get($payload, 'TransID'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }
}
