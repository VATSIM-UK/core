<?php

namespace App\Services\Training\DTO;

use App\Models\Mship\Qualification;

class ManualAtcUpgradeResult
{
    public function __construct(
        public bool $upgraded,
        public ?Qualification $qualification = null
    ) {}

    public static function noUpgradeAvailable(): self
    {
        return new self(false);
    }

    public static function upgraded(Qualification $qualification): self
    {
        return new self(true, $qualification);
    }
}
