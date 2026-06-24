<?php

namespace App\Enums;

/**
 * Enumeration for contribution frequency.
 */
enum ContributionFrequency: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Annual = 'annual';
    case OneTime = 'one_time';
}
