<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Services\GroupOwnerService;
use App\Features\Groups\Models\Group;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\PermissionRegistrar;

/**
 * HTTP controller for Impersonation.
 */
class ImpersonationController extends Controller
{
    public function __construct(
        private readonly GroupOwnerService $groupOwnerService,
    ) {}

    /**
     * Login as.
     */
    public function loginAs(Group $group): RedirectResponse
    {
        $user = $this->groupOwnerService->findOwnerUser($group);

        if (! $user) {
            return back()->withErrors(['group' => 'No active user found for this group.']);
        }

        Auth::guard('super_admin')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        Auth::login($user);
        request()->session()->regenerate();

        app(PermissionRegistrar::class)->setPermissionsTeamId($group->id);

        return redirect()->route('portal.dashboard');
    }
}
