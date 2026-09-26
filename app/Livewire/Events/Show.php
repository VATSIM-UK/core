<?php

declare(strict_types=1);

namespace App\Livewire\Events;

use App\Models\Events\Event;
use App\Services\Events\EventCalendarService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('livewire.events.layout')]
class Show extends Component
{
    public Event $event;

    public function mount(Event $event): void
    {
        abort_unless($event->isPublished(), 404);

        $this->event = $event->load(['positions', 'managers']);
    }

    public function downloadIcs(EventCalendarService $calendar)
    {
        return response()->streamDownload(
            fn () => print ($calendar->ics($this->event)),
            $calendar->icsFilename($this->event),
            ['Content-Type' => 'text/calendar'],
        );
    }

    public function render(EventCalendarService $calendar)
    {
        return view('livewire.events.show', [
            'googleUrl' => $calendar->google($this->event),
            'yahooUrl' => $calendar->yahoo($this->event),
            'outlookUrl' => $calendar->webOutlook($this->event),
            'officeUrl' => $calendar->webOffice($this->event),
        ])->layoutData([
            '_pageTitle' => $this->event->name,
            '_breadcrumb' => [
                ['name' => 'Events', 'uri' => route('site.events.index')],
                ['name' => $this->event->name, 'uri' => route('site.events.show', $this->event)],
            ],
        ]);
    }
}
