<?php

declare(strict_types=1);

namespace App\Services\Discord;

enum CidLookupStatus
{
    case Invalid;
    case NotFound;
    case NotLinked;
    case Found;
}
