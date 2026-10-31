<?php

declare(strict_types=1);

namespace App\Services\Mship;

final readonly class StaffDirectoryEntry
{
    /**
     * @param  list<string>  $jobTitles
     */
    public function __construct(
        public string $name,
        public array $jobTitles,
        public int $sortOrder,
        public string $sortName,
    ) {}
}
