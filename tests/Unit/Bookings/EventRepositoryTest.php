<?php

namespace Tests\Unit\Bookings;

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
}
