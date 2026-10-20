<?php

declare(strict_types=1);

namespace App\Support\Calendar;

use Carbon\Carbon;

final readonly class IcsEvent
{
    public function __construct(
        public string $uid,
        public string $summary,
        public Carbon $startsAt,
        public Carbon $endsAt,
        public string $description = '',
        public string $location = '',
    ) {}
}
