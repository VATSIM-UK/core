<?php

declare(strict_types=1);

namespace App\Enums;

enum VTCheckStatus: string
{
    case Pending = 'pending';
    case Failed = 'failed';
    case Passed = 'passed';
    case NotRequired = 'not_required';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Failed => 'Failed',
            self::Passed => 'Passed',
            self::NotRequired => 'Not Required'
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Passed => 'success',
            self::Failed => 'danger',
            self::NotRequired => 'gray',
            self::Pending => 'warning',
        };
    }
}
