<?php

namespace App\Models\Training\WaitingList;

class Removal
{
    public function __construct(public RemovalReason $reason, public ?int $removedBy, public ?string $otherReason = '') {}

    public function comment(): string
    {
        if ($this->reason == RemovalReason::Other) {
            return $this->otherReason ?? '';
        }

        return $this->reason->label();
    }
}
