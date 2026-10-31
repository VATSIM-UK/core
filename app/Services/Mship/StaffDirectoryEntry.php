<?php

declare(strict_types=1);

namespace App\Services\Mship;

use Illuminate\Support\Str;

final readonly class StaffDirectoryEntry
{
    public string $initials;

    /**
     * @param  list<string>  $jobTitles
     */
    public function __construct(
        public string $name,
        public array $jobTitles,
        public int $sortOrder,
        public string $sortName,
    ) {
        $this->initials = $this->initialsFor($name);
    }

    private function initialsFor(string $name): string
    {
        if ($name === 'Vacant') {
            return '—';
        }

        return Str::initials(
            Str::words(Str::squish($name), 2, ''),
            capitalize: true,
        );
    }
}
