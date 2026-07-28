<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

class PositionHelper
{
    public static function extractAirfieldIcao(string $callsign): ?string
    {
        $part = Str::before($callsign, '_');

        if (strlen($part) === 4 && ctype_upper($part)) {
            return $part;
        }

        return null;
    }
}
