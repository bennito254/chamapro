<?php

namespace App\Enums;

/**
 * Enumeration for loan status.
 */
enum LoanStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
    case Defaulted = 'defaulted';
}
