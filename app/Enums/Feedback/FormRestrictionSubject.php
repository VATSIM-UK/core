<?php

namespace App\Enums\Feedback;

enum FormRestrictionSubject: string
{
    case Atc = 'atc';
    case Pilot = 'pilot';

    public function label(): string
    {
        return match ($this) {
            self::Atc => 'ATC',
            self::Pilot => 'Pilot',
        };
    }
}
