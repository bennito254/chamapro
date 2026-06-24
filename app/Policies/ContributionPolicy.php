<?php

namespace App\Policies;

use App\Features\Contributions\Models\Contribution;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

/**
 * Authorization policy for Contribution.
 */
class ContributionPolicy
{
    private function setTeam(User $user): void
    {
        if ($user->group_id) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->group_id);
        }
    }

    /**
     * View any.
     */
    public function viewAny(User $user): bool
    {
        $this->setTeam($user);

        return $user->can('contributions.view');
    }

    /**
     * View.
     */
    public function view(User $user, Contribution $contribution): bool
    {
        $this->setTeam($user);

        return $user->can('contributions.view');
    }

    /**
     * Create.
     */
    public function create(User $user): bool
    {
        $this->setTeam($user);

        return $user->can('contributions.record');
    }

    /**
     * Update.
     */
    public function update(User $user, Contribution $contribution): bool
    {
        $this->setTeam($user);

        return $user->can('contributions.record');
    }

    /**
     * Delete.
     */
    public function delete(User $user, Contribution $contribution): bool
    {
        $this->setTeam($user);

        return $user->can('contributions.record');
    }
}
