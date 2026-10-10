<?php

declare(strict_types=1);

namespace App\Enums;

enum TheoryExamReminderStatus: string
{
    case Pending = 'pending';
    case Passed = 'passed';
    case Removed = 'removed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting Exam',
            self::Passed => 'Exam Passed',
            self::Removed => 'Removed (No Attempt)',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Passed => 'success',
            self::Removed => 'danger',
            self::Cancelled => 'gray',
        };
    }
}
