<?php

declare(strict_types=1);

namespace Tests\Unit\Events;

use App\Models\Atc\Position;
use App\Models\Events\Event;
use App\Services\Events\EventCalendarService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    private EventCalendarService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = resolve(EventCalendarService::class);
    }

    private function event(array $attributes = []): Event
    {
        return Event::factory()->create(array_merge([
            'name' => 'Lorem Ipsum Dolor',
            'tagline' => 'Consectetur adipiscing elit sed do eiusmod.',
            'description' => '<p>Ut enim ad <strong>minim</strong> veniam.</p>',
            'start' => Carbon::parse('2026-10-18 18:00:00', 'UTC'),
            'end' => Carbon::parse('2026-10-18 22:00:00', 'UTC'),
        ], $attributes));
    }

    public function test_it_builds_a_google_calendar_url(): void
    {
        $this->assertStringStartsWith('https://calendar.google.com', $this->service->google($this->event()));
    }

    public function test_it_builds_a_yahoo_calendar_url(): void
    {
        $this->assertStringStartsWith('https://calendar.yahoo.com', $this->service->yahoo($this->event()));
    }

    public function test_it_builds_an_outlook_web_url(): void
    {
        $this->assertStringStartsWith('https://outlook.live.com', $this->service->webOutlook($this->event()));
    }

    public function test_it_builds_an_outlook_desktop_url(): void
    {
        $this->assertStringStartsWith('https://outlook.cloud.microsoft', $this->service->webOffice($this->event()));
    }

    public function test_it_produces_an_ics_calendar_containing_the_event(): void
    {
        $ics = $this->service->ics($this->event());

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('Lorem Ipsum Dolor', $ics);
        $this->assertStringContainsString('DTSTART:20261018T180000Z', $ics);
        $this->assertStringContainsString('DTEND:20261018T220000Z', $ics);
    }

    public function test_ics_description_is_the_tagline_and_not_the_event_description(): void
    {
        $ics = $this->service->ics($this->event());

        $this->assertStringContainsString('Consectetur adipiscing elit sed do eiusmod.', $ics);
        $this->assertStringNotContainsString('Ut enim ad', $ics);
        $this->assertStringNotContainsString('<strong>', $ics);
    }

    public function test_ics_does_not_include_the_covered_positions(): void
    {
        $event = $this->event();
        $event->positions()->attach(Position::factory()->create(['callsign' => 'EGGW_TWR'])->id);

        $ics = $this->service->ics($event->fresh());

        $this->assertStringNotContainsString('EGGW_TWR', $ics);
        $this->assertStringNotContainsString('LOCATION:', $ics);
    }

    public function test_it_builds_a_slugged_ics_filename(): void
    {
        $this->assertSame('vatsim-uk-lorem-ipsum-dolor.ics', $this->service->icsFilename($this->event()));
    }

    public function test_it_falls_back_to_an_event_id_ics_filename_when_the_name_has_no_slug(): void
    {
        $event = $this->event(['name' => '???']);

        $this->assertSame('vatsim-uk-event-'.$event->id.'.ics', $this->service->icsFilename($event));
    }
}
