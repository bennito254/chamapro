<?php

namespace App\Policies;

use App\Features\Members\Models\Member;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

/**
 * Authorization policy for Member.
 */
class MemberPolicy
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

        return $user->can('members.view');
    }

    /**
     * View.
     */
    public function view(User $user, Member $member): bool
    {
        $this->setTeam($user);

        return $user->can('members.view');
    }

    /**
     * Create.
     */
    public function create(User $user): bool
    {
        $this->setTeam($user);

        return $user->can('members.manage');
    }

    /**
     * Update.
     */
    public function update(User $user, Member $member): bool
    {
        $this->setTeam($user);

        return $user->can('members.manage');
    }

    /**
     * Delete.
     */
    public function delete(User $user, Member $member): bool
    {
        $this->setTeam($user);

        return $user->can('members.manage');
    }

    /**
     * Import.
     */
    public function import(User $user): bool
    {
        $this->setTeam($user);

        return $user->can('members.import');
    }
}
