<?php

namespace App\Features\Admin\Controllers;

use App\Enums\SubscriptionPaymentStatus;
use App\Features\Admin\Services\PlatformMpesaSettingsService;
use App\Features\Groups\Models\Group;
use App\Features\Subscriptions\Models\Subscription;
use App\Features\Subscriptions\Models\SubscriptionPayment;
use App\Features\Support\Models\SupportTicket;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Admin Dashboard.
 */
class AdminDashboardController extends Controller
{
    public function __construct(
        private PlatformMpesaSettingsService $mpesaSettings,
    ) {}

    /**
     * Index.
     */
    public function index(): Response
    {
        $mpesa = $this->mpesaSettings->all();

        $stats = [
            'groups_total' => Group::count(),
            'groups_active' => Group::where('status', 'active')->count(),
            'groups_suspended' => Group::where('status', 'suspended')->count(),
            'subscriptions_active' => Subscription::where('status', 'active')->count(),
            'subscriptions_trial' => Subscription::where('status', 'trial')->count(),
            'subscriptions_expired' => Subscription::where('status', 'expired')->count(),
            'open_tickets' => SupportTicket::where('status', 'open')->count(),
            'payments_completed' => SubscriptionPayment::where('status', SubscriptionPaymentStatus::Completed)->count(),
            'payments_pending' => SubscriptionPayment::where('status', SubscriptionPaymentStatus::Pending)->count(),
        ];

        $recentGroups = Group::query()
            ->with('activeSubscription.plan')
            ->latest()
            ->limit(10)
            ->get();

        return Inertia::render('admin/dashboard', [
            'stats' => $stats,
            'recentGroups' => $recentGroups,
            'mpesaSettings' => [
                'mpesa_consumer_key' => $mpesa[PlatformMpesaSettingsService::KEY_CONSUMER_KEY],
                'mpesa_consumer_secret' => $mpesa[PlatformMpesaSettingsService::KEY_CONSUMER_SECRET],
                'mpesa_shortcode' => $mpesa[PlatformMpesaSettingsService::KEY_SHORTCODE],
                'mpesa_passkey' => $mpesa[PlatformMpesaSettingsService::KEY_PASSKEY],
                'mpesa_callback_url' => $mpesa[PlatformMpesaSettingsService::KEY_CALLBACK_URL],
                'mpesa_environment' => $mpesa[PlatformMpesaSettingsService::KEY_ENVIRONMENT],
                'mpesa_stk_enabled' => in_array(strtolower($mpesa[PlatformMpesaSettingsService::KEY_STK_ENABLED]), ['1', 'true', 'yes', 'on'], true),
                'configured' => $this->mpesaSettings->isConfigured(),
                'stub_mode' => $this->mpesaSettings->usesStubMode(),
            ],
        ]);
    }
}
