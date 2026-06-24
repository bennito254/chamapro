<?php

declare(strict_types=1);

namespace App\Features\Sms\Services;

use App\Features\Admin\Models\SmsProvider;
use App\Features\Members\Models\Member;
use App\Features\Sms\Contracts\SmsDriver;
use App\Features\Sms\Drivers\AfricasTalkingDriver;
use App\Features\Sms\Drivers\BulkSmsDriver;
use App\Features\Sms\Drivers\HttpDriver;
use App\Features\Sms\Drivers\LogDriver;
use App\Features\Sms\Drivers\TwilioDriver;
use App\Features\Sms\DTOs\SmsSendResult;
use App\Features\Sms\Models\SmsMessage;
use App\Features\Sms\Models\SmsTemplate;
use App\Models\User;
use App\Support\GroupContext;
use InvalidArgumentException;

/**
 * Manager for related drivers or resources for Sms Gateway.
 */
class SmsGatewayManager
{
    /**
     * Create a new instance.
     */
    public function __construct(private GroupContext $groupContext) {}

    /**
     * Send a message for a specific group (platform/admin broadcasts).
     */
    public function sendForGroup(
        int $groupId,
        string $recipient,
        string $body,
        ?SmsProvider $provider = null,
        ?User $sender = null,
    ): SmsMessage {
        $provider ??= $this->defaultProvider();

        $result = $this->driver($provider)->send($recipient, $body);

        return SmsMessage::withoutGlobalScopes()->create([
            'group_id' => $groupId,
            'member_id' => null,
            'sms_template_id' => null,
            'recipient' => $recipient,
            'body' => $body,
            'provider' => $provider->driver,
            'status' => $result->success ? 'sent' : 'failed',
            'delivered_at' => $result->success ? now() : null,
            'error_message' => $result->error,
            'sent_by' => $sender?->id,
        ]);
    }

    /**
     * Send.
     */
    public function send(
        string $recipient,
        string $body,
        ?SmsProvider $provider = null,
        ?Member $member = null,
        ?SmsTemplate $template = null,
        ?User $sender = null,
    ): SmsMessage {
        $provider ??= $this->defaultProvider();

        $result = $this->driver($provider)->send($recipient, $body);

        return SmsMessage::create([
            'group_id' => $this->groupContext->id(),
            'member_id' => $member?->id,
            'sms_template_id' => $template?->id,
            'recipient' => $recipient,
            'body' => $body,
            'provider' => $provider->driver,
            'status' => $result->success ? 'sent' : 'failed',
            'delivered_at' => $result->success ? now() : null,
            'error_message' => $result->error,
            'sent_by' => $sender?->id,
        ]);
    }

    /**
     * Driver.
     */
    public function driver(SmsProvider $provider): SmsDriver
    {
        $credentials = $provider->credentials ?? [];

        return match ($provider->driver) {
            'log', 'dummy' => new LogDriver($credentials),
            'africas_talking', 'africastalking' => new AfricasTalkingDriver($credentials),
            'twilio' => new TwilioDriver($credentials),
            'bulksms' => new BulkSmsDriver($credentials),
            'http' => new HttpDriver($credentials),
            default => throw new InvalidArgumentException("Unsupported SMS driver [{$provider->driver}]."),
        };
    }

    /**
     * Send raw.
     */
    public function sendRaw(SmsProvider $provider, string $recipient, string $body): SmsSendResult
    {
        return $this->driver($provider)->send($recipient, $body);
    }

    /**
     * Default provider.
     */
    public function defaultProvider(): SmsProvider
    {
        $provider = SmsProvider::query()
            ->where('is_default', true)
            ->where('status', 'active')
            ->first();

        if ($provider) {
            return $provider;
        }

        return SmsProvider::query()->firstOrCreate(
            ['driver' => 'log', 'name' => 'Dummy Log Provider'],
            [
                'credentials' => [],
                'is_default' => true,
                'status' => 'active',
            ],
        );
    }
}
