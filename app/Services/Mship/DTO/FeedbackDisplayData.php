<?php

namespace App\Services\Mship\DTO;

use Illuminate\Support\Collection;

class FeedbackDisplayData
{
    public function __construct(
        public bool $canDisplay,
        public ?Collection $feedback = null,
        public ?string $errorMessage = null
    ) {}
}
