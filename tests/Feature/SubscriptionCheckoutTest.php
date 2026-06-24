<?php

use App\Enums\SubscriptionPaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Features\Admin\Services\PlatformMpesaSettingsService;
use App\Features\Auth\Models\SuperAdmin;
use App\Features\Groups\Models\Group;
use App\Features\Groups\Services\GroupProvisioningService;
use App\Features\Mpesa\Models\MpesaTransaction;
use App\Features\Subscriptions\Models\Subscription;
use App\Features\Subscriptions\Models\SubscriptionPayment;
use App\Features\Subscriptions\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\PlatformMpesaSettingsSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed([
        SubscriptionPlanSeeder::class,
        PlatformMpesaSettingsSeeder::class,
        RolesAndPermissionsSeeder::class,
    ]);
});

function createRenewalUser(Group $group): User
{
    $user = User::factory()->create([
        'group_id' => $group->id,
        'email_verified_at' => now(),
    ]);

    app(RolesAndPermissionsSeeder::class)->seedForGroup($group);
    app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);
    $user->assignRole('Chairperson');

    return $user;
}

test('subscription renewal initiates mpesa checkout in stub mode and renews subscription', function () {
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create(['phone' => '0712345678']);
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    $group->activeSubscription->update([
        'status' => SubscriptionStatus::Expired,
        'end_date' => now()->subDay(),
    ]);

    $user = createRenewalUser($group);

    $this->actingAs($user)
        ->post(route('portal.subscription.renew.store'), [
            'subscription_plan_id' => $plan->id,
            'phone_number' => '0712345678',
        ])
        ->assertRedirect(route('portal.dashboard'));

    $payment = SubscriptionPayment::first();

    expect($payment)->not->toBeNull()
        ->and($payment->status)->toBe(SubscriptionPaymentStatus::Completed)
        ->and($payment->mpesa_receipt_number)->not->toBeNull();

    $active = $group->fresh()->activeSubscription;

    expect($active->status)->toBe(SubscriptionStatus::Active)
        ->and(Subscription::where('group_id', $group->id)->count())->toBe(2);
});

test('mpesa callback completes pending subscription payment', function () {
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    $payment = SubscriptionPayment::create([
        'group_id' => $group->id,
        'subscription_plan_id' => $plan->id,
        'phone_number' => '254712345678',
        'amount' => $plan->amount,
        'status' => SubscriptionPaymentStatus::Pending,
        'checkout_request_id' => 'CHKTEST123456',
    ]);

    $transaction = MpesaTransaction::withoutGlobalScopes()->create([
        'group_id' => $group->id,
        'transaction_id' => 'CHKTEST123456',
        'phone_number' => '254712345678',
        'amount' => $plan->amount,
        'type' => 'subscription_renewal',
        'status' => 'pending',
        'payable_type' => SubscriptionPayment::class,
        'payable_id' => $payment->id,
    ]);

    $payment->update(['mpesa_transaction_id' => $transaction->id]);

    $this->postJson(route('mpesa.callback'), [
        'Body' => [
            'stkCallback' => [
                'CheckoutRequestID' => 'CHKTEST123456',
                'ResultCode' => 0,
                'CallbackMetadata' => [
                    'Item' => [
                        ['Name' => 'MpesaReceiptNumber', 'Value' => 'QAB123XYZ'],
                    ],
                ],
            ],
        ],
    ])->assertSuccessful();

    $payment->refresh();

    expect($payment->status)->toBe(SubscriptionPaymentStatus::Completed)
        ->and($payment->mpesa_receipt_number)->toBe('QAB123XYZ')
        ->and($transaction->fresh()->status)->toBe('completed');
});

test('super admin can view subscription payments filtered by status', function () {
    $admin = SuperAdmin::factory()->create();
    $plan = SubscriptionPlan::first();
    $group = Group::factory()->create();
    app(GroupProvisioningService::class)->provisionExisting($group, $plan);

    SubscriptionPayment::create([
        'group_id' => $group->id,
        'subscription_plan_id' => $plan->id,
        'phone_number' => '254700000001',
        'amount' => $plan->amount,
        'status' => SubscriptionPaymentStatus::Completed,
        'paid_at' => now(),
        'mpesa_receipt_number' => 'RCPT001',
    ]);

    SubscriptionPayment::create([
        'group_id' => $group->id,
        'subscription_plan_id' => $plan->id,
        'phone_number' => '254700000002',
        'amount' => $plan->amount,
        'status' => SubscriptionPaymentStatus::Failed,
    ]);

    $this->actingAs($admin, 'super_admin')
        ->get(route('admin.subscription-payments.index', ['status' => 'completed']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/subscription-payments/index')
            ->has('payments.data', 1)
            ->where('payments.data.0.status', 'completed'));
});

test('super admin can update mpesa settings from dashboard endpoint', function () {
    $admin = SuperAdmin::factory()->create();

    $this->actingAs($admin, 'super_admin')
        ->put(route('admin.mpesa-settings.update'), [
            'mpesa_consumer_key' => 'test-key',
            'mpesa_consumer_secret' => 'test-secret',
            'mpesa_shortcode' => '174379',
            'mpesa_passkey' => 'test-passkey',
            'mpesa_callback_url' => 'https://example.com/api/mpesa/callback',
            'mpesa_environment' => 'sandbox',
            'mpesa_stk_enabled' => true,
        ])
        ->assertRedirect();

    $settings = app(PlatformMpesaSettingsService::class)->all();

    expect($settings['mpesa_consumer_key'])->toBe('test-key')
        ->and($settings['mpesa_shortcode'])->toBe('174379');
});
