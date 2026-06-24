<?php

namespace App\Enums;

/**
 * Enumeration for account type.
 */
enum AccountType: string
{
    case Asset = 'asset';
    case Liability = 'liability';
    case Income = 'income';
    case Expense = 'expense';
    case Equity = 'equity';
}
