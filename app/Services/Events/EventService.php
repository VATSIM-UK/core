<?php

namespace App\Services\Events;

use App\Models\Events\Event;

class EventService
{
    public function publish(Event $event): array
    {
        $event->published_at = $event->published_at ?? now();
        $event->save();

        return $event->unpublishedChecklist();
    }
}
