<?php

declare(strict_types=1);

namespace App\Exceptions\Bookings;

use App\Models\Events\Event;
use RuntimeException;

class PositionRosteredException extends RuntimeException
{
    public function __construct(public readonly Event $event)
    {
        parent::__construct("This position is rostered for {$event->name} and cannot be booked.");
    }
}
