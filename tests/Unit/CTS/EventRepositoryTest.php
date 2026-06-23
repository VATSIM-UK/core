<?php

namespace Tests\Unit\CTS;

use App\Models\Cts\Event;
use App\Repositories\Cts\EventRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EventRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = resolve(EventRepository::class);
    }

    #[Test]
    public function it_can_return_todays_events()
    {
        Event::factory()->count(2)->create(['date' => Carbon::now()->addDays(5)->toDateString()]);

        $eventTodayOne = Event::Factory()->create([
            'date' => Carbon::now()->toDateString(),
            'from' => Carbon::now()->addSecond()->toTimeString(),
        ]);

        $eventTodayTwo = Event::Factory()->create([
            'date' => Carbon::now()->toDateString(),
            'from' => Carbon::now()->addSecond()->toTimeString(),
        ]);

        $events = $this->repository->getTodaysEvents();

        $this->assertInstanceOf(Collection::class, $events);
        $this->assertCount(2, $events);
        $this->assertEquals($events->first()->event, $eventTodayOne->event);
        $this->assertEquals($events->last()->event, $eventTodayTwo->event);
    }

    #[Test]
    public function it_can_return_the_next_upcoming_event()
    {
        $pastEvent = Event::factory()->create([
            'date' => Carbon::now()->subDays(1)->toDateString(),
        ]);

        $nextEvent = Event::factory()->create([
            'date' => Carbon::now()->addDays(2)->toDateString(),
            'from' => '14:00:00',
        ]);

        Event::factory()->create([
            'date' => Carbon::now()->addDays(5)->toDateString(),
            'from' => '10:00:00',
        ]);

        $result = $this->repository->getNextEvent();

        $this->assertInstanceOf(Event::class, $result);
        $this->assertEquals($nextEvent->event, $result->event);
        $this->assertEquals($nextEvent->date->toDateString(), $result->date->toDateString());
    }

    #[Test]
    public function it_returns_null_when_no_future_events_exist()
    {
        Event::factory()->create([
            'date' => Carbon::now()->subDays(1)->toDateString(),
        ]);

        $result = $this->repository->getNextEvent();

        $this->assertNull($result);
    }

    #[Test]
    public function it_returns_the_earliest_future_event()
    {
        $earliestEvent = Event::factory()->create([
            'date' => Carbon::now()->addDays(3)->toDateString(),
            'from' => '09:00:00',
        ]);

        Event::factory()->create([
            'date' => Carbon::now()->addDays(5)->toDateString(),
            'from' => '10:00:00',
        ]);

        $result = $this->repository->getNextEvent();

        $this->assertEquals($earliestEvent->event, $result->event);
    }

    #[Test]
    public function it_ignores_events_marked_as_gone()
    {
        Event::factory()->create([
            'date' => Carbon::now()->addDays(1)->toDateString(),
            'gone' => 1,
        ]);

        Event::factory()->create([
            'date' => Carbon::now()->addDays(2)->toDateString(),
            'gone' => 0,
        ]);

        $result = $this->repository->getNextEvent();

        $this->assertNotNull($result);
        $this->assertEquals(0, $result->gone);
    }

    #[Test]
    public function it_can_return_todays_event_as_next_event()
    {
        $todayEvent = Event::factory()->create([
            'date' => Carbon::now()->toDateString(),
            'from' => Carbon::now()->addHour()->toTimeString(),
        ]);

        $result = $this->repository->getNextEvent();

        $this->assertInstanceOf(Event::class, $result);
        $this->assertEquals($todayEvent->event, $result->event);
    }
}
