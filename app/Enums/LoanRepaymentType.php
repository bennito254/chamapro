<?php

namespace App\Enums;

/**
 * Enumeration for loan repayment type.
 */
enum LoanRepaymentType: string
{
    case Combined = 'combined';
    case Interest = 'interest';
    case Principal = 'principal';
}
