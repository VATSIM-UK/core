<?php

namespace App\Services\Training\Availability;

use App\Models\Training\Availability;
use Carbon\Carbon;

final readonly class AvailabilityChange
{
    /**
     * @param  array<int, Availability>  $swallowed  Rows deleted as part of a merge.
     */
    public function __construct(
        public AvailabilityChangeType $type,
        public ?Availability $existing,
        public ?Carbon $oldFrom,
        public ?Carbon $oldTo,
        public ?Carbon $newFrom,
        public ?Carbon $newTo,
        public array $swallowed = [],
    ) {}
}
