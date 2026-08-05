<?php

declare(strict_types=1);

namespace App\Enums;

enum AvailabilityLogEvent: string
{
    case Added = 'added';
    case Merged = 'merged';
    case Edited = 'edited';

    public function label(): string
    {
        return match ($this) {
            self::Added => 'Added',
            self::Merged => 'Merged',
            self::Edited => 'Edited',
        };
    }
}
