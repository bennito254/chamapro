<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Spatie\Permission\PermissionRegistrar;

/**
 * Application class for handle inertia requests.
 */
class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    /**
     * Version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $superAdmin = $request->user('super_admin');

        if ($user?->group_id) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->group_id);
        }

        $subscription = $user?->group?->activeSubscription;
        if ($subscription) {
            $subscription->loadMissing('plan');
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'superAdmin' => $superAdmin,
            ],
            'group' => $user?->group,
            'subscription' => $subscription,
            'permissions' => $user instanceof User ? $user->getAllPermissions()->pluck('name') : [],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
