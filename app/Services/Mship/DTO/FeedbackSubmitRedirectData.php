<?php

namespace App\Services\Mship\DTO;

class FeedbackSubmitRedirectData
{
    /**
     * @param  array<string, mixed>  $errors
     */
    public function __construct(
        public bool $useBackRedirect,
        public string $route,
        public ?string $message = null,
        public array $errors = [],
        public bool $withInput = false
    ) {}
}

