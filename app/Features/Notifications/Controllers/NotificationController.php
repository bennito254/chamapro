<?php

namespace App\Features\Notifications\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP controller for Notification.
 */
class NotificationController extends Controller
{
    /**
     * Index.
     */
    public function index(Request $request): Response
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return Inertia::render('portal/notifications/index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark read.
     */
    public function markRead(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return back();
    }
}
