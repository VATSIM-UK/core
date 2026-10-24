<?php

namespace App\Repositories\Events;

use App\Models\Events\Event;
use App\Support\Bookings\BookingDays;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
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
     * Published events that have not yet finished, soonest first.
     *
     * @return Collection<int, Event>
     */
    public function getUpcoming(): Collection
    {
        return Event::published()
            ->upcoming()
            ->orderBy('start')
            ->with('positions')
            ->get();
    }

    /**
     * Published events that have finished, most recent first.
     */
    public function getPast(int $perPage = 12): LengthAwarePaginator
    {
        return Event::published()
            ->past()
            ->orderByDesc('start')
            ->with('positions')
            ->paginate($perPage);
    }

    /**
     * Published events for the calendar, shaped like booking rows so the
     * calendar timeline can render them in its events row.
     *
     * @param  string  $timezone  Timezone the date is in and the returned times are expressed in
     * @return Collection<int, object>
     */
    public function getEventsForDate(Carbon $date, string $timezone = 'UTC'): Collection
    {
        return $this->getEventsForRange($date, $date, $timezone);
    }

    /**
     * @param  string  $timezone  Timezone the dates are in and the returned times are expressed in
     * @return Collection<int, object>
     */
    public function getEventsForRange(Carbon $start, Carbon $end, string $timezone = 'UTC'): Collection
    {
        return Event::published()
            ->where('start', '<', Carbon::parse($end->toDateString(), $timezone)->addDay()->utc())
            ->where('end', '>', Carbon::parse($start->toDateString(), $timezone)->utc())
            ->orderBy('start')
            ->get()
            ->flatMap(function (Event $event) use ($start, $end, $timezone): array {
                $starts = $event->start->copy()->setTimezone($timezone);
                $ends = $event->end->copy()->setTimezone($timezone);

                return BookingDays::split((object) [
                    'id' => (string) $event->id,
                    'source' => 'event',
                    'cts_booking_id' => null,
                    'position_id' => null,
                    'position' => null,
                    'date' => $starts->format('Y-m-d'),
                    'from' => $starts->format('H:i'),
                    'to' => $ends->format('H:i'),
                    'ends_on' => $ends->format('Y-m-d'),
                    'type' => 'EV',
                    'member' => ['cid' => '', 'display_name' => 'Unknown'],
                    'event_name' => $event->name,
                    'event_url' => route('site.events.show', $event),
                ], $start->toDateString(), $end->toDateString());
            })
            ->values();
    }
}
