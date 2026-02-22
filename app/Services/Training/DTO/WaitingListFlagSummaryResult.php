<?php

namespace App\Services\Training\DTO;

class WaitingListFlagSummaryResult
{
    /**
     * @param  array<string, mixed>|null  $summary
     */
    public function __construct(public ?array $summary) {}

    /**
     * @return array{summary: array<string, mixed>|null}
     */
    public function toArray(): array
    {
        return ['summary' => $this->summary];
    }
}
