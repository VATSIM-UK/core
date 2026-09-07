<?php

namespace App\Repositories\Events;

use App\Models\Events\Event;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EventRepository
{
    public function getNextEvent(): ?Event
    {
        return Event::published()
            ->upcoming()
            ->orderBy('start')
            ->first();
    }

    public function getTodaysEvents(): Collection
    {
        return Event::published()
            ->whereDate('start', today()->toDateString())
            ->orderBy('start')
            ->get();
    }

    /**
     * Published events for the calendar, shaped like booking rows so the
     * calendar timeline can render them in its events row.
     *
     * @return Collection<int, object>
     */
    public function getEventsForDate(Carbon $date): Collection
    {
        return Event::published()
            ->whereDate('start', $date->toDateString())
            ->orderBy('start')
            ->get()
            ->map(fn (Event $event): object => (object) [
                'id' => (string) $event->id,
                'source' => 'event',
                'cts_booking_id' => null,
                'position_id' => null,
                'position' => null,
                'date' => $event->start->format('Y-m-d'),
                'from' => $event->start->format('H:i'),
                'to' => $event->end->format('H:i'),
                'type' => 'EV',
                'member' => ['cid' => '', 'display_name' => 'Unknown'],
                'event_name' => $event->name,
            ]);
    }
}
