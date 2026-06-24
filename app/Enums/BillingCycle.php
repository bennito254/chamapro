<?php

namespace App\Enums;

/**
 * Enumeration for billing cycle.
 */
enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Annual = 'annual';
}
