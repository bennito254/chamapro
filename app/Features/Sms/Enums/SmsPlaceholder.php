<?php

declare(strict_types=1);

namespace App\Features\Sms\Enums;

/**
 * Enumeration for sms placeholder.
 */
enum SmsPlaceholder: string
{
    case Name = 'name';
    case MembershipNumber = 'membership_number';
    case Phone = 'phone';
    case GroupName = 'group_name';
    case ContributionsMissed = 'contributions_missed';
    case ContributionsDue = 'contributions_due';
    case PrincipalBalance = 'principal_balance';
    case InterestBalance = 'interest_balance';
    case LoanBalance = 'loan_balance';
    case UnpaidFines = 'unpaid_fines';

    /**
     * @return array<int, array{key: string, label: string, description: string}>
     */
    public static function definitions(): array
    {
        return [
            ['key' => self::Name->value, 'label' => 'Member name', 'description' => 'Full name of the member'],
            ['key' => self::MembershipNumber->value, 'label' => 'Membership #', 'description' => 'Member membership number'],
            ['key' => self::Phone->value, 'label' => 'Phone', 'description' => 'Member phone number'],
            ['key' => self::GroupName->value, 'label' => 'Group name', 'description' => 'Chama name'],
            ['key' => self::ContributionsMissed->value, 'label' => 'Contributions missed', 'description' => 'Count of unmet contribution types at the latest meeting'],
            ['key' => self::ContributionsDue->value, 'label' => 'Contributions due', 'description' => 'Total contribution shortfall at the latest meeting'],
            ['key' => self::PrincipalBalance->value, 'label' => 'Principal balance', 'description' => 'Outstanding loan principal'],
            ['key' => self::InterestBalance->value, 'label' => 'Interest balance', 'description' => 'Outstanding loan interest'],
            ['key' => self::LoanBalance->value, 'label' => 'Loan balance', 'description' => 'Total outstanding loan balance'],
            ['key' => self::UnpaidFines->value, 'label' => 'Unpaid fines', 'description' => 'Total unpaid fine amount'],
        ];
    }
}
