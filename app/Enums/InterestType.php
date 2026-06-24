<?php

namespace App\Enums;

/**
 * Enumeration for interest type.
 */
enum InterestType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';
}
