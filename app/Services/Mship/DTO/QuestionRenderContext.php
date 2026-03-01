<?php

namespace App\Services\Mship\DTO;

class QuestionRenderContext
{
    /**
     * @param  array<string, mixed>  $oldInput
     */
    public function __construct(
        public array $oldInput = [],
        public ?string $cid = null
    ) {}

    public function old(string $key, mixed $default = null): mixed
    {
        return $this->oldInput[$key] ?? $default;
    }
}
