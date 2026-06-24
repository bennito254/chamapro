<?php

namespace App\Policies;

use App\Features\Sms\Models\SmsTemplate;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

/**
 * Authorization policy for Sms Template.
 */
class SmsTemplatePolicy
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

        return $user->can('sms.view');
    }

    /**
     * View.
     */
    public function view(User $user, SmsTemplate $smsTemplate): bool
    {
        $this->setTeam($user);

        return $user->can('sms.view');
    }

    /**
     * Create.
     */
    public function create(User $user): bool
    {
        $this->setTeam($user);

        return $user->can('sms.manage');
    }

    /**
     * Update.
     */
    public function update(User $user, SmsTemplate $smsTemplate): bool
    {
        $this->setTeam($user);

        return $user->can('sms.manage');
    }

    /**
     * Delete.
     */
    public function delete(User $user, SmsTemplate $smsTemplate): bool
    {
        $this->setTeam($user);

        return $user->can('sms.manage');
    }
}
