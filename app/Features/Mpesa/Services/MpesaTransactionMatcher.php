<?php

declare(strict_types=1);

namespace App\Features\Mpesa\Services;

use App\Features\Members\Models\Member;

/**
 * mpesa feature: mpesa transaction matcher.
 */
class MpesaTransactionMatcher
{
    /**
     * Match by phone.
     */
    public function matchByPhone(string $phoneNumber): ?Member
    {
        $normalized = $this->normalizePhone($phoneNumber);

        if ($normalized === '') {
            return null;
        }

        return Member::query()
            ->get(['id', 'phone_number'])
            ->first(fn (Member $member): bool => $this->normalizePhone((string) $member->phone_number) === $normalized);
    }

    /**
     * Normalize phone.
     */
    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '254')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '254'.substr($digits, 1);
        }

        if (strlen($digits) === 9) {
            return '254'.$digits;
        }

        return $digits;
    }
}
