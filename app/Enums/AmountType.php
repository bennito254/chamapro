<?php

namespace App\Enums;

/**
 * Enumeration for amount type.
 */
enum AmountType: string
{
    case Fixed = 'fixed';
    case Variable = 'variable';
}
