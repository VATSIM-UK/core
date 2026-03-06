<?php

namespace App\Services\VisitTransfer\DTO;

class RefereeAddExceptionData
{
    public function __construct(
        public string $message,
        public bool $useBackRedirect
    ) {}
}
