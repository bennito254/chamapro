<?php

namespace App\DTOs;

/**
 * Data transfer object for a single journal entry line.
 */
readonly class JournalLineDTO
{
    /**
     * Create a new instance.
     */
    public function __construct(
        public string $accountCode,
        public float $debit = 0,
        public float $credit = 0,
    ) {}
}
