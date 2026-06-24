<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Application class for ensure subscription writable.
 */
class EnsureSubscriptionWritable
{
    /**
     * Handle.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        if ($request->routeIs('portal.subscription.renew.store')) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user || ! $user->group) {
            return $next($request);
        }

        $subscription = $user->group->activeSubscription;

        if (! $subscription || ! $subscription->isWritable()) {
            return redirect()->route('portal.subscription.renew')
                ->with('error', 'Your subscription has expired. Please renew to continue recording transactions.');
        }

        return $next($request);
    }
}
