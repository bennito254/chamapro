<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

/**
 * Authorization policy for portal reports.
 */
class ReportPolicy
{
    private function setTeam(User $user): void
    {
        if ($user->group_id) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->group_id);
        }
    }

    /**
     * Determine whether the user can view reports.
     */
    public function viewAny(User $user): bool
    {
        $this->setTeam($user);

        return $user->can('reports.view');
    }

    /**
     * Determine whether the user can export reports.
     */
    public function export(User $user): bool
    {
        $this->setTeam($user);

        return $user->can('reports.export');
    }
}
