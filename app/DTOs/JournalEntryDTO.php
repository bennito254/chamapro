<?php

namespace App\DTOs;

/**
 * Data transfer object for a journal entry and its lines.
 */
readonly class JournalEntryDTO
{
    /**
     * @param  array<int, JournalLineDTO>  $lines
     */
    public function __construct(
        public string $description,
        public string $date,
        public array $lines,
        public ?string $sourceType = null,
        public ?int $sourceId = null,
        public ?int $recordedBy = null,
    ) {}
}
