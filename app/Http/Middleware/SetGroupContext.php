<?php

namespace App\Http\Middleware;

use App\Support\GroupContext;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Application class for set group context.
 */
class SetGroupContext
{
    /**
     * Handle.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $groupContext = app(GroupContext::class);
        $user = $request->user();

        if ($user && $user->group_id) {
            $user->loadMissing('group.activeSubscription.plan');
            $groupContext->set($user->group);

            app(PermissionRegistrar::class)->setPermissionsTeamId($user->group_id);
        }

        return $next($request);
    }
}
