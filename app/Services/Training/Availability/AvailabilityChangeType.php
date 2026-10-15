<?php

namespace App\Services\Training\Availability;

enum AvailabilityChangeType: string
{
    case Added = 'added';
    case Edited = 'edited';
    case Merged = 'merged';
    case Removed = 'removed';
    case Unchanged = 'unchanged';
}
