<?php

namespace Tests\Unit\Events;

use App\Models\Events\Event;
use App\Repositories\Events\EventRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EventRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = resolve(EventRepository::class);
    }

    public function test_get_next_event_returns_soonest_published_upcoming_event(): void
    {
        $sooner = Event::factory()->create(['start' => now()->addDay()->startOfHour(), 'end' => now()->addDay()->startOfHour()->addHours(2), 'published_at' => now()]);
        Event::factory()->create(['start' => now()->addDays(3)->startOfHour(), 'end' => now()->addDays(3)->startOfHour()->addHours(2), 'published_at' => now()]);

        $this->assertEquals($sooner->id, $this->repository->getNextEvent()->id);
    }

    public function test_get_next_event_excludes_drafts(): void
    {
        Event::factory()->create(['start' => now()->addDay()->startOfHour(), 'end' => now()->addDay()->startOfHour()->addHours(2), 'published_at' => null]);

        $this->assertNull($this->repository->getNextEvent());
    }

    public function test_get_todays_events_returns_only_published_events_today(): void
    {
        Event::factory()->create(['start' => now()->startOfDay()->addHours(2), 'end' => now()->startOfDay()->addHours(5), 'published_at' => now()]);
        Event::factory()->create(['start' => now()->startOfDay()->addHours(6), 'end' => now()->startOfDay()->addHours(9), 'published_at' => null]);
        Event::factory()->create(['start' => now()->addDay()->startOfHour(), 'end' => now()->addDay()->startOfHour()->addHours(2), 'published_at' => now()]);

        $events = $this->repository->getTodaysEvents();

        $this->assertCount(1, $events);
    }

    public function test_get_events_for_date_returns_ev_shaped_published_events(): void
    {
        $date = Carbon::parse('2026-08-01');

        Event::factory()->create([
            'name' => 'Test event',
            'start' => $date->copy()->setTime(18, 0),
            'end' => $date->copy()->setTime(22, 0),
            'published_at' => now(),
        ]);
        Event::factory()->create([
            'start' => $date->copy()->setTime(20, 0),
            'end' => $date->copy()->setTime(23, 0),
            'published_at' => null,
        ]);

        $events = $this->repository->getEventsForDate($date);

        $this->assertCount(1, $events);
        $event = $events->first();
        $this->assertSame('EV', $event->type);
        $this->assertSame('event', $event->source);
        $this->assertSame('Test event', $event->event_name);
        $this->assertSame('18:00', $event->from);
        $this->assertSame('22:00', $event->to);
        $this->assertSame('2026-08-01', $event->date);
        $this->assertNull($event->position);
        $this->assertSame('Unknown', $event->member['display_name']);
    }
}
