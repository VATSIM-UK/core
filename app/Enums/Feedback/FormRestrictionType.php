<?php

namespace App\Enums\Feedback;

enum FormRestrictionType: string
{
    case Qualification = 'qualification';
    case Hours = 'hours';

    public function label(): string
    {
        return match ($this) {
            self::Qualification => 'Minimum qualification',
            self::Hours => 'Minimum hours',
        };
    }
}
