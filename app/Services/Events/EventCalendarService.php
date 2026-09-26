<?php

declare(strict_types=1);

namespace App\Services\Events;

use App\Models\Events\Event;
use Spatie\CalendarLinks\Link;

class EventCalendarService
{
    public function link(Event $event): Link
    {
        // Only the tagline goes out to calendars - not the rich description and
        // not the covered positions.
        $link = Link::create($event->name, $event->start, $event->end);
        $tagline = trim((string) $event->tagline);

        return $tagline === '' ? $link : $link->description($tagline);
    }

    public function google(Event $event): string
    {
        return $this->link($event)->google();
    }

    public function yahoo(Event $event): string
    {
        return $this->link($event)->yahoo();
    }

    public function webOutlook(Event $event): string
    {
        return $this->link($event)->webOutlook();
    }

    public function webOffice(Event $event): string
    {
        return $this->link($event)->webOffice();
    }

    public function ics(Event $event): string
    {
        return $this->link($event)->ics([], ['format' => 'file']);
    }

    public function icsFilename(Event $event): string
    {
        $slug = (string) str($event->name)->slug();

        return $slug === ''
            ? 'vatsim-uk-event-'.$event->id.'.ics'
            : 'vatsim-uk-'.$slug.'.ics';
    }
}
