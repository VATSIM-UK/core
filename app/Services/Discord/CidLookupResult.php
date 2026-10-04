<?php

declare(strict_types=1);

namespace App\Services\Discord;

final class CidLookupResult
{
    public function __construct(
        public readonly CidLookupStatus $status,
        public readonly ?string $discordId = null,
    ) {}
}
