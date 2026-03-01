<?php

declare(strict_types=1);

namespace App\Enums;

enum AvailabilityCheckStatus: string
{
    case Passed = 'passed';
    case Failed = 'failed';
    case OnLeave = 'on_leave';

    public function label(): string
    {
        return match ($this) {
            self::Passed => 'Passed',
            self::Failed => 'Failed',
            self::OnLeave => 'On leave',
        };
    }
}
