<?php

declare(strict_types=1);

namespace App\Enums;

enum SeminarTheoryReminderStatus: string
{
    case Pending = 'pending';
    case Passed = 'passed';
    case Removed = 'removed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Passed => 'Passed',
            self::Removed => 'Removed (Theory Not Passed)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Passed => 'success',
            self::Removed => 'danger',
        };
    }
}
