<?php

namespace App\Support;

use App\Features\Groups\Models\Group;

/**
 * Application class for group context.
 */
class GroupContext
{
    private ?Group $group = null;

    /**
     * Set.
     */
    public function set(?Group $group): void
    {
        $this->group = $group;
    }

    /**
     * Get.
     */
    public function get(): ?Group
    {
        return $this->group;
    }

    /**
     * Id.
     */
    public function id(): ?int
    {
        return $this->group?->id;
    }

    /**
     * Is active.
     */
    public function isActive(): bool
    {
        return $this->group !== null;
    }
}
