<?php

declare(strict_types=1);

namespace App\Services\Mship;

final readonly class StaffDepartmentDirectory
{
    /**
     * @param  list<StaffDirectoryEntry>  $leads
     * @param  list<StaffDirectoryEntry>  $team
     */
    public function __construct(
        public array $leads,
        public array $team,
    ) {}
}
