<?php

namespace Tests\Unit\Events;

use App\Models\Events\Event;
use App\Repositories\Events\EventRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function test_get_upcoming_returns_published_events_not_yet_ended_soonest_first(): void
    {
        $soon = Event::factory()->create(['start' => now()->addDay(), 'end' => now()->addDay()->addHours(2), 'published_at' => now()]);
        $later = Event::factory()->create(['start' => now()->addDays(3), 'end' => now()->addDays(3)->addHours(2), 'published_at' => now()]);
        Event::factory()->create(['start' => now()->subDays(2), 'end' => now()->subDay(), 'published_at' => now()]); // past
        Event::factory()->create(['start' => now()->addDay(), 'end' => now()->addDay()->addHours(2), 'published_at' => null]); // draft

        $upcoming = $this->repository->getUpcoming();

        $this->assertSame([$soon->id, $later->id], $upcoming->pluck('id')->all());
    }

    public function test_get_upcoming_includes_an_event_happening_right_now(): void
    {
        $inProgress = Event::factory()->create(['start' => now()->subHour(), 'end' => now()->addHour(), 'published_at' => now()]);

        $this->assertSame([$inProgress->id], $this->repository->getUpcoming()->pluck('id')->all());
    }

    public function test_get_past_returns_published_ended_events_most_recent_first(): void
    {
        $recent = Event::factory()->create(['start' => now()->subDays(2), 'end' => now()->subDay(), 'published_at' => now()]);
        $older = Event::factory()->create(['start' => now()->subDays(10), 'end' => now()->subDays(9), 'published_at' => now()]);
        Event::factory()->create(['start' => now()->addDay(), 'end' => now()->addDay()->addHours(2), 'published_at' => now()]); // upcoming
        Event::factory()->create(['start' => now()->subDays(3), 'end' => now()->subDays(3)->addHours(2), 'published_at' => null]); // draft

        $past = $this->repository->getPast();

        $this->assertSame([$recent->id, $older->id], $past->pluck('id')->all());
    }

    public function test_get_past_paginates_results_and_reports_the_total(): void
    {
        foreach (range(1, 15) as $i) {
            Event::factory()->create([
                'start' => now()->subDays($i + 1),
                'end' => now()->subDays($i),
                'published_at' => now(),
            ]);
        }

        $past = $this->repository->getPast(10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $past);
        $this->assertCount(10, $past);
        $this->assertSame(15, $past->total());
    }
}
