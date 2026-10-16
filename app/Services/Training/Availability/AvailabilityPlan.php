<?php

namespace App\Services\Training\Availability;

final readonly class AvailabilityPlan
{
    /**
     * @param  array<int, AvailabilityChange>  $changes
     */
    public function __construct(
        public array $changes,
        public int $skippedShortRuns,
    ) {}
}
