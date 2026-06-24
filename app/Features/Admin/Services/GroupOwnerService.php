<?php

declare(strict_types=1);

namespace App\Features\Admin\Services;

use App\Features\Groups\Models\Group;
use App\Models\User;

/**
 * Resolves the primary contact details for a group owner (chairperson).
 */
class GroupOwnerService
{
    /**
     * @return array{name: ?string, email: ?string, phone: ?string}
     */
    public function resolve(?Group $group): array
    {
        if ($group === null) {
            return [
                'name' => null,
                'email' => null,
                'phone' => null,
            ];
        }

        $user = $this->resolveOwnerUser($group);

        return [
            'name' => $user?->name,
            'email' => $user?->email ?? $group->email,
            'phone' => $group->phone,
        ];
    }

    /**
     * Find the primary portal user for a group (chairperson, or first active user).
     */
    public function findOwnerUser(Group $group): ?User
    {
        return $this->resolveOwnerUser($group);
    }

    private function resolveOwnerUser(Group $group): ?User
    {
        $chairperson = User::query()
            ->where('group_id', $group->id)
            ->where('status', 'active')
            ->whereHas('roles', function ($query) use ($group): void {
                $query->where('roles.name', 'Chairperson')
                    ->where('roles.group_id', $group->id);
            })
            ->first();

        if ($chairperson !== null) {
            return $chairperson;
        }

        return User::query()
            ->where('group_id', $group->id)
            ->where('status', 'active')
            ->orderBy('id')
            ->first();
    }
}
