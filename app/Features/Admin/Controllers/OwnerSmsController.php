<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Requests\SendOwnerSmsRequest;
use App\Features\Admin\Services\AdminOwnerSmsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

/**
 * HTTP controller for broadcasting SMS to group owners.
 */
class OwnerSmsController extends Controller
{
    /**
     * Create a new instance.
     */
    public function __construct(private AdminOwnerSmsService $ownerSmsService) {}

    /**
     * Show the compose form.
     */
    public function create(Request $request): Response
    {
        $status = $request->string('subscription_status', 'all')->toString();

        return Inertia::render('admin/owner-sms/create', [
            'subscriptionStatus' => $status,
            'recipientCount' => $this->ownerSmsService->recipientCount($status),
            'statusOptions' => [
                ['value' => 'all', 'label' => 'All groups'],
                ['value' => 'trial', 'label' => 'Trial'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'expired', 'label' => 'Expired'],
                ['value' => 'suspended', 'label' => 'Suspended'],
            ],
            'recentMessages' => $this->ownerSmsService->recentMessages(),
        ]);
    }

    /**
     * Send SMS to matching group owners.
     */
    public function store(SendOwnerSmsRequest $request): RedirectResponse
    {
        try {
            $result = $this->ownerSmsService->send(
                $request->validated('subscription_status'),
                $request->validated('body'),
            );
        } catch (InvalidArgumentException $exception) {
            return back()->withErrors(['subscription_status' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.owner-sms.create', ['subscription_status' => $request->validated('subscription_status')])
            ->with('success', "SMS sent to {$result['sent']} owner(s). Skipped {$result['skipped']} without phone. Failed {$result['failed']}.");
    }
}
