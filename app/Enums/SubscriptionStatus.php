<?php

namespace App\Enums;

/**
 * Enumeration for subscription status.
 */
enum SubscriptionStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case Expired = 'expired';
    case Suspended = 'suspended';

    /**
     * Is writable.
     */
    public function isWritable(): bool
    {
        return in_array($this, [self::Trial, self::Active], true);
    }

    /**
     * Allows login.
     */
    public function allowsLogin(): bool
    {
        return $this !== self::Suspended;
    }
}
