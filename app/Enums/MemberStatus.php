<?php

namespace App\Enums;

/**
 * Enumeration for member status.
 */
enum MemberStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Exited = 'exited';
    case Deceased = 'deceased';
}
