<?php

namespace App\Http\Middleware;

use App\Enums\SubscriptionStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Application class for ensure subscription active.
 */
class EnsureSubscriptionActive
{
    /**
     * Handle.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->group) {
            return $next($request);
        }

        $subscription = $user->group->activeSubscription;

        if ($subscription && $subscription->status === SubscriptionStatus::Suspended) {
            abort(403, 'Your group subscription has been suspended. Please contact support.');
        }

        return $next($request);
    }
}
