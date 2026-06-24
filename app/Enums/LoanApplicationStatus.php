<?php

namespace App\Enums;

/**
 * Enumeration for loan application status.
 */
enum LoanApplicationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Recommended = 'recommended';
    case Rejected = 'rejected';
    case Approved = 'approved';
    case Disbursed = 'disbursed';
    case Closed = 'closed';
}
