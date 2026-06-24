<?php

namespace App\Policies;

use App\Features\Loans\Models\LoanApplication;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

/**
 * Authorization policy for Loan Application.
 */
class LoanApplicationPolicy
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

        return $user->can('loans.apply') || $user->can('loans.review') || $user->can('loans.approve');
    }

    /**
     * View.
     */
    public function view(User $user, LoanApplication $loanApplication): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Create.
     */
    public function create(User $user): bool
    {
        $this->setTeam($user);

        return $user->can('loans.apply');
    }

    /**
     * Update.
     */
    public function update(User $user, LoanApplication $loanApplication): bool
    {
        $this->setTeam($user);

        return $user->can('loans.apply') || $user->can('loans.review') || $user->can('loans.approve');
    }

    /**
     * Delete.
     */
    public function delete(User $user, LoanApplication $loanApplication): bool
    {
        $this->setTeam($user);

        return $user->can('loans.apply');
    }

    /**
     * Transition.
     */
    public function transition(User $user, LoanApplication $loanApplication): bool
    {
        $this->setTeam($user);

        return $user->can('loans.review') || $user->can('loans.approve') || $user->can('loans.disburse');
    }
}
